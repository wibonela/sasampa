// Lists the clips, voiceovers and music present in public/ so the video falls back gracefully when one is missing.
import { existsSync, readdirSync, writeFileSync } from "node:fs";

const list = (rel, re) => {
  const dir = new URL(`../public/${rel}/`, import.meta.url);
  return existsSync(dir) ? readdirSync(dir).filter((f) => re.test(f)) : [];
};
const sub = (rel) => {
  const dir = new URL(`../public/${rel}/`, import.meta.url);
  return existsSync(dir) ? readdirSync(dir, { withFileTypes: true }).filter((d) => d.isDirectory()).map((d) => d.name) : [];
};

const out = { clips: {}, audio: {}, music: list("audio", /\.(m4a|mp3|wav)$/i) };
for (const p of sub("clips")) out.clips[p] = list(`clips/${p}`, /\.(mp4|mov|webm)$/i);
for (const p of sub("audio")) out.audio[p] = list(`audio/${p}`, /\.(m4a|mp3|wav)$/i);

writeFileSync(new URL("../data/available.json", import.meta.url), JSON.stringify(out, null, 2));
console.log("clips:", Object.entries(out.clips).map(([k, v]) => `${k}=${v.length}`).join(", ") || "none");
console.log("voiceover:", Object.entries(out.audio).map(([k, v]) => `${k}=${v.length}`).join(", ") || "none", "| music:", out.music.join(", ") || "none");
