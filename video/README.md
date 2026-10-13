# Sasampa videos

Remotion project that turns real recordings of the mobile app into captioned, branded videos.
The rules every video must meet are in [STYLE.md](STYLE.md) and are enforced by `npm run check`.

| Project | What | Length |
|---------|------|--------|
| `launch` | Launch video (Kiswahili captions, English voiceover) | ~80s |
| `tutorial-sale` | Tutorial: how to make a sale in 5 steps | ~56s |

## Everyday commands

```bash
npm install
npm run studio                       # live preview in the browser
npm run check launch                 # the clean-standard gate
npm run render:launch                # out/launch-9x16.mp4
npm run render:launch:wide           # out/launch-16x9.mp4
npm run render:tutorial-sale         # (and :wide)
```

Needs `ffmpeg` (`brew install ffmpeg`), Xcode with an iOS simulator, and Flutter.
`remotion.config.ts` uses the installed Google Chrome because Remotion's bundled browser crashes on some Macs.

## Re-recording from the real app

```bash
scripts/record.sh launch             # or: tutorial-sale
```

This starts a throwaway demo server (fake data from `demo/seed_demo.php`), erases and boots the "Sasampa Demo" simulator
with a clean 9:41 status bar, drives the app with the matching test in `mobile_app/integration_test/` while recording,
fails if the run shows overflows or missing screens, and cuts one clip per step into `public/clips/<project>/`
(updating each step's `seconds` in `data/<project>.json`).

## Audio

```bash
node scripts/make-voiceover.mjs launch        # English voiceover from the "vo" lines (macOS `say`)
node scripts/make-music.mjs                   # original background track -> public/audio/music.m4a
```

Swap any generated file for your own recording or licensed track by putting a file with the same name in `public/audio/`.
Recorded clips and generated audio are not committed; the scripts rebuild them.

## Adding a video

See the checklist at the bottom of [STYLE.md](STYLE.md).
