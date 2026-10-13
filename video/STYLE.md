# Sasampa video standard

Every video (launch, tutorials, anything later) follows this. `npm run render:<project>` runs `scripts/check.mjs` first and
**refuses to render** if the rules marked (checked) are broken. The numbers live in `data/style.json`.

## Look
- Colours from the app: red `#FF3B30` accent, iOS blue `#007AFF`, dark navy `#0A1020` background, white text. One source: `src/brand.ts`.
- Font: system sans (SF Pro), bold captions, never more than 2 lines.
- Real app only, shown inside the phone frame. No mock-ups, no invented screens.
- Structure: title card (intro) -> numbered steps with progress bar -> end card with the real logo.
- 9:16 master, plus a 16:9 version of every video.

## Words
- Captions are Kiswahili, short and in the app's own wording (`mobile_app/lib/l10n/app_sw.arb`). (checked: max 62 characters)
- Tutorial steps are written as "Hatua N: ..." and each tutorial is 5 steps or fewer.
- Never promote something the app does not do yet. The banned list is in `data/style.json` (currently WhatsApp receipts, which the app shows as "coming soon"). (checked)
- A Swahili speaker reviews every caption before a video is published.

## Timing
- Each step 5-16 seconds. Launch video at most 90s, tutorials at most 120s. (checked)
- Footage must be at least as long as its step, otherwise it would freeze. (checked)
- Slow, deliberate taps; pause about a second on every result screen.

## Footage (recorded by `scripts/record.sh`, never by hand)
- iPhone 17 Pro simulator, freshly erased each run, status bar fixed at 9:41, full battery, full signal.
- Fake data only: the demo duka "Duka la Mama Neema" from `demo/seed_demo.php`. Never production data, never real customers.
- Local throwaway server and database; mail goes to the log; no real keys.
- No debug artifacts: the run fails on any layout overflow (yellow/black stripes), missing screen or stuck screen. (checked, in `record.sh`)
- Keyboard down before the result screen. Portrait phone ratio. (checked)

## Audio
- Voiceover: English, generated from the `vo` lines (`scripts/make-voiceover.mjs`, macOS voice Samantha), normalised to -16 LUFS. A real recording can replace any file in `public/audio/<project>/` with the same name. Each line must be 0.5s shorter than its scene. (checked)
- Music: an original synthesised track (`scripts/make-music.mjs`), about -18 LUFS, mixed low and lower again under voiceover, with a fade in and out. Replace `public/audio/music.m4a` with any licensed track you own; never use music you do not have rights to.

## New video checklist
1. Add `data/<name>.json` (copy `tutorial-sale.json`) and register it in `src/data.ts`.
2. Write a test in `mobile_app/integration_test/` using `support/recorder.dart`, and add it to the `case` in `scripts/record.sh`.
3. `scripts/record.sh <name>` (must finish with "Cleanliness check" passing).
4. `node scripts/make-voiceover.mjs <name>`.
5. `npm run render <name>` and `npm run render <name> wide` (the check runs for you).
6. Watch it once, have the captions reviewed, then publish.
