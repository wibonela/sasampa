// Generates an English voiceover per scene from the "vo" text in data/<project>.json using macOS `say`.
//   node scripts/make-voiceover.mjs <project> [voice]
// To use a real recording instead, drop m4a/mp3/wav files with the same names (intro, <beat id>, end) into public/audio/<project>/.
import { execFileSync } from "node:child_process";
import { mkdirSync, mkdtempSync, readFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";

const [project, voice = "Samantha"] = process.argv.slice(2);
if (!project) throw new Error("usage: make-voiceover.mjs <project> [voice]");
const data = JSON.parse(readFileSync(new URL(`../data/${project}.json`, import.meta.url), "utf8"));
const outDir = new URL(`../public/audio/${project}/`, import.meta.url).pathname;
mkdirSync(outDir, { recursive: true });
const tmp = mkdtempSync(join(tmpdir(), "vo-"));

const scenes = [
  { id: "intro", seconds: data.intro.seconds, vo: data.intro.vo },
  ...data.beats.map((b) => ({ id: b.id, seconds: b.seconds, vo: b.vo })),
  { id: "end", seconds: data.endCard.seconds, vo: data.endCard.vo },
];

for (const s of scenes) {
  if (!s.vo) continue;
  const aiff = join(tmp, `${s.id}.aiff`);
  const out = join(outDir, `${s.id}.m4a`);
  execFileSync("say", ["-v", voice, "-r", "165", "-o", aiff, s.vo]);
  execFileSync("ffmpeg", ["-v", "error", "-y", "-i", aiff, "-af", "loudnorm=I=-16:TP=-1.5:LRA=11", "-c:a", "aac", "-b:a", "160k", out]);
  const len = Number(execFileSync("ffprobe", ["-v", "error", "-show_entries", "format=duration", "-of", "csv=p=0", out]).toString());
  const fits = len <= s.seconds - 0.6;
  console.log(`${s.id.padEnd(11)} voice ${len.toFixed(1)}s / scene ${s.seconds}s ${fits ? "ok" : "TOO LONG, shorten the vo text"}`);
}
