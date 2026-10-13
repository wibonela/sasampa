// The "clean standard" gate. Run before every render: node scripts/check.mjs <project>
// Rules live in data/style.json and are explained in STYLE.md.
import { execFileSync } from "node:child_process";
import { existsSync, readFileSync } from "node:fs";

const project = process.argv[2];
if (!project) throw new Error("usage: check.mjs <project>");
const rel = (p) => new URL(`../${p}`, import.meta.url).pathname;
const data = JSON.parse(readFileSync(rel(`data/${project}.json`), "utf8"));
const style = JSON.parse(readFileSync(rel("data/style.json"), "utf8"));
const errors = [], warnings = [];
const probe = (file, entries, stream) =>
  execFileSync("ffprobe", ["-v", "error", ...(stream ? ["-select_streams", stream] : []), "-show_entries", entries, "-of", "csv=p=0", file]).toString().trim();

// Captions: short, no banned claims
const texts = [
  ["intro", `${data.intro.line1} ${data.intro.line2}`],
  ...data.beats.map((b) => [b.id, b.caption]),
  ["end", `${data.endCard.title} ${data.endCard.subtitle}`],
];
for (const b of data.beats) {
  if (b.caption.length > style.maxCaptionChars) errors.push(`${b.id}: caption has ${b.caption.length} characters (max ${style.maxCaptionChars})`);
  if (b.seconds < style.beatSeconds[0] || b.seconds > style.beatSeconds[1]) warnings.push(`${b.id}: ${b.seconds}s is outside ${style.beatSeconds.join("-")}s`);
}
for (const [id, text] of texts) {
  for (const { word, why } of style.bannedClaims) {
    if (text.toLowerCase().includes(word.toLowerCase())) errors.push(`${id}: mentions "${word}". ${why}`);
  }
}
for (const [id, vo] of [["intro", data.intro.vo], ...data.beats.map((b) => [b.id, b.vo]), ["end", data.endCard.vo]]) {
  for (const { word, why } of style.bannedClaims) if (vo && vo.toLowerCase().includes(word.toLowerCase())) errors.push(`${id}: voiceover mentions "${word}". ${why}`);
}

// Length
const total = data.intro.seconds + data.beats.reduce((s, b) => s + b.seconds, 0) + data.endCard.seconds;
const max = style.maxTotalSeconds[project] ?? 120;
if (total > max) errors.push(`total ${total.toFixed(1)}s is over the ${max}s limit`);

// Footage: present, portrait phone ratio, long enough, recorded cleanly
const clipDir = `public/clips/${project}`;
for (const b of data.beats) {
  const file = ["mp4", "mov", "webm"].map((e) => rel(`${clipDir}/${b.id}.${e}`)).find(existsSync);
  if (!file) { errors.push(`${b.id}: no clip in ${clipDir}/ (run scripts/record.sh ${project})`); continue; }
  const [w, h] = probe(file, "stream=width,height", "v").split(",").map(Number);
  const ratio = w / h;
  if (ratio < style.phoneAspect[0] || ratio > style.phoneAspect[1]) errors.push(`${b.id}: clip is ${w}x${h}, expected a portrait phone recording`);
  const len = Number(probe(file, "format=duration"));
  if (len < b.seconds - 0.3) errors.push(`${b.id}: clip is ${len.toFixed(1)}s but the scene is ${b.seconds}s (it would freeze)`);
}
const recMeta = rel(`${clipDir}/_recording.json`);
if (!existsSync(recMeta)) warnings.push("clips were not produced by scripts/record.sh, so the clean-recording check cannot be confirmed");
else if (!JSON.parse(readFileSync(recMeta, "utf8")).clean) errors.push("recording was not marked clean");

// Audio
if (data.voiceover) {
  const scenes = [["intro", data.intro.seconds], ...data.beats.map((b) => [b.id, b.seconds]), ["end", data.endCard.seconds]];
  for (const [id, secs] of scenes) {
    const f = ["m4a", "mp3", "wav"].map((e) => rel(`public/audio/${project}/${id}.${e}`)).find(existsSync);
    if (!f) { warnings.push(`${id}: no voiceover file (run scripts/make-voiceover.mjs ${project})`); continue; }
    const len = Number(probe(f, "format=duration"));
    if (len > secs - 0.5) errors.push(`${id}: voiceover is ${len.toFixed(1)}s, scene is ${secs}s`);
  }
}
if (data.music && !existsSync(rel(`public/${data.music.file}`))) errors.push(`music file public/${data.music.file} is missing (run scripts/make-music.mjs)`);

for (const w of warnings) console.log(`warning: ${w}`);
for (const e of errors) console.log(`ERROR: ${e}`);
console.log(`${project}: ${total.toFixed(1)}s, ${errors.length} error(s), ${warnings.length} warning(s)`);
process.exit(errors.length ? 1 : 0);
