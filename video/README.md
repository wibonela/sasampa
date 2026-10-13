# Sasampa launch video

Remotion project. Captions, durations and the end card live in `data/launch.json`; clips go in `public/clips/`.

```bash
npm install
npm run studio        # live preview
npm run render        # out/launch-9x16.mp4 (vertical)
npm run render:wide   # out/launch-16x9.mp4
```

Needs `ffmpeg` (`brew install ffmpeg`). `remotion.config.ts` uses the installed Google Chrome because the bundled browser crashes on some Macs.

## Recording the clips from the real app (iOS simulator)

1. Start a local demo server on a throwaway database with fake data (never production data):
   `DB_DATABASE=<scratch.sqlite> MAIL_MAILER=log php artisan serve --port=8099`
2. Boot an iPhone simulator whose iOS runtime matches Xcode's SDK, and set a clean status bar:
   `xcrun simctl status_bar booted override --time 9:41 --batteryState charged --batteryLevel 100`
3. Record while the app drives itself:
   ```bash
   xcrun simctl io booted recordVideo --codec=h264 raw.mp4 &
   cd ../mobile_app
   flutter test integration_test/demo_recording_test.dart -d <sim id> \
     --dart-define=API_BASE_URL=http://127.0.0.1:8099/api/v1
   kill -INT %1
   ```
   The test prints `MARK <beat> start|end <epoch ms>`; cut each beat out of `raw.mp4` with `ffmpeg -ss <start> -t <length>`.
