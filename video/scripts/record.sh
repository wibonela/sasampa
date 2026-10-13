#!/usr/bin/env bash
# Records one video project from the real app on an iOS simulator.
#   scripts/record.sh launch | tutorial-sale | tutorial-orders | tutorial-customers | tutorial-stock | tutorial-expenses
# Starts a throwaway demo server (fake data), resets the simulator, drives the app with the
# matching integration test while recording, then cuts one clip per beat into public/clips/<project>/.
# It refuses to produce clips if the run showed layout overflows or missing screens (the "clean standard").
set -euo pipefail

PROJECT=${1:?usage: scripts/record.sh <launch|tutorial-NAME>}
HERE=$(cd "$(dirname "$0")/.." && pwd)
ROOT=$(cd "$HERE/.." && pwd)
WORK=$HERE/.demo
PORT=8099

case "$PROJECT" in
  launch) TEST=integration_test/demo_recording_test.dart ;;
  tutorial-*) TEST=integration_test/tutorial_$(echo "${PROJECT#tutorial-}" | tr '-' '_')_test.dart ;;
  *) echo "Unknown project: $PROJECT" >&2; exit 1 ;;
esac

mkdir -p "$WORK"
SERVER=""; RECPID=""
cleanup() { [ -n "$RECPID" ] && kill -INT "$RECPID" 2>/dev/null || true; [ -n "$SERVER" ] && kill "$SERVER" 2>/dev/null || true; }
trap cleanup EXIT

echo "==> Demo server on a throwaway database"
rm -f "$WORK/demo.sqlite"; touch "$WORK/demo.sqlite"
cd "$ROOT"
export DB_CONNECTION=sqlite DB_DATABASE="$WORK/demo.sqlite" MAIL_MAILER=log QUEUE_CONNECTION=sync \
       CACHE_STORE=array SESSION_DRIVER=array RESEND_API_KEY=
php artisan migrate --force >/dev/null
php artisan tinker --execute="require '$HERE/demo/seed_demo.php';" | grep seeded
php artisan serve --host=127.0.0.1 --port=$PORT >"$WORK/serve.log" 2>&1 &
SERVER=$!
for _ in $(seq 1 30); do curl -sf "http://127.0.0.1:$PORT/up" >/dev/null && break; sleep 1; done

echo "==> Simulator (app reset, clean status bar)"
SIM=$(xcrun simctl list devices | grep "Sasampa Demo" | grep -oE '[0-9A-F]{8}-([0-9A-F]{4}-){3}[0-9A-F]{12}' | head -1 || true)
NEW_SIM=0
if [ -z "$SIM" ]; then
  RUNTIME=$(xcrun simctl list runtimes | grep -oE 'com.apple.CoreSimulator.SimRuntime.iOS-[0-9-]+' | sort -V | tail -1)
  SIM=$(xcrun simctl create "Sasampa Demo" com.apple.CoreSimulator.SimDeviceType.iPhone-17-Pro "$RUNTIME")
  NEW_SIM=1
fi
xcrun simctl boot "$SIM" 2>/dev/null || true
xcrun simctl bootstatus "$SIM" -b >/dev/null
# Reset the app and its login instead of erasing the whole simulator: a freshly erased iPhone shows a one-off
# iOS banner ("Ready for Apple Intelligence") that would end up in the footage.
xcrun simctl uninstall "$SIM" com.sasampa.pos 2>/dev/null || true
xcrun simctl keychain "$SIM" reset
if [ "$NEW_SIM" = 1 ]; then echo "New simulator: waiting 120s for first-boot notifications to pass"; sleep 120; fi
xcrun simctl status_bar "$SIM" override --time 9:41 --batteryState charged --batteryLevel 100 --cellularBars 4 --wifiBars 3
open -a Simulator

echo "==> Recording $PROJECT"
rm -f "$WORK/raw.mp4"
xcrun simctl io "$SIM" recordVideo --codec=h264 --force "$WORK/raw.mp4" >"$WORK/rec.log" 2>&1 &
RECPID=$!
sleep 2
REC0=$(node -e 'console.log(Date.now())')
cd "$ROOT/mobile_app"
# The app has a debug-only assertion at login, so the exit code is not a reliable signal; the log checks below are.
flutter test "$TEST" -d "$SIM" --dart-define=API_BASE_URL=http://127.0.0.1:$PORT/api/v1 --no-pub \
  >"$WORK/$PROJECT.log" 2>&1 || true
# Stop the recorder and wait until it has finished writing the file (a long recording takes a while to finalise).
kill -INT "$RECPID" 2>/dev/null || true
for _ in $(seq 1 120); do kill -0 "$RECPID" 2>/dev/null || break; sleep 1; done
RECPID=""
echo "$REC0" > "$WORK/rec0.txt"

echo "==> Cleanliness check"
if grep -qE "overflowed|NOT FOUND|STUCK" "$WORK/$PROJECT.log"; then
  echo "NOT CLEAN. Fix these before using the footage:" >&2
  grep -E "overflowed|NOT FOUND|STUCK" "$WORK/$PROJECT.log" | cut -c1-200 >&2
  exit 1
fi
if ! grep -q "^MARK" "$WORK/$PROJECT.log"; then echo "No MARK lines: the test did not run." >&2; tail -20 "$WORK/$PROJECT.log" >&2; exit 1; fi

echo "==> Cutting clips"
node "$HERE/scripts/cut-clips.mjs" "$PROJECT" "$WORK/raw.mp4" "$WORK/$PROJECT.log" "$REC0"
echo "Done. Next: cd video && npm run check && npm run render"
