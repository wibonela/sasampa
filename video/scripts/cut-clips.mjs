// Cuts one clip per beat out of a raw simulator recording, using the MARK lines printed by the test.
//   node scripts/cut-clips.mjs <project> <raw.mp4> <test.log> <recording start epoch ms>
import { execFileSync } from "node:child_process";
import { mkdirSync, readFileSync, writeFileSync } from "node:fs";

const [project, raw, log, rec0Arg] = process.argv.slice(2);
if (!rec0Arg) throw new Error("usage: cut-clips.mjs <project> <raw.mp4> <test.log> <rec0>");
const rec0 = Number(rec0Arg);

const dataPath = new URL(`../data/${project}.json`, import.meta.url);
const data = JSON.parse(readFileSync(dataPath, "utf8"));
const marks = {};
for (const line of readFileSync(log, "utf8").split("\n")) {
  const m = line.match(/^MARK (\S+) (start|end) (\d+)/);
  if (m) (marks[m[1]] ??= {})[m[2]] = (Number(m[3]) - rec0) / 1000;
}

const outDir = new URL(`../public/clips/${project}/`, import.meta.url);
mkdirSync(outDir, { recursive: true });

for (const beat of data.beats) {
  const mk = marks[beat.id];
  if (!mk || mk.start === undefined || mk.end === undefined) throw new Error(`No start/end marks for beat ${beat.id}`);
  const speed = beat.speed ?? 1;
  const length = mk.end - mk.start;
  const out = new URL(`${beat.id}.mp4`, outDir).pathname;
  execFileSync("ffmpeg", [
    "-v", "error", "-y", "-ss", mk.start.toFixed(2), "-t", length.toFixed(2), "-i", raw,
    "-an", "-vf", `setpts=PTS/${speed},fps=30`,
    "-c:v", "libx264", "-crf", "18", "-preset", "medium", "-pix_fmt", "yuv420p", "-movflags", "+faststart", out,
  ]);
  // Keep the edit timing equal to the real clip length.
  beat.seconds = Math.round((length / speed) * 10) / 10;
  console.log(`${beat.id}: ${beat.seconds}s${speed !== 1 ? ` (x${speed})` : ""}`);
}

writeFileSync(dataPath, JSON.stringify(data, null, 2) + "\n");
writeFileSync(new URL("_recording.json", outDir), JSON.stringify({ recordedAt: new Date().toISOString(), source: "ios-simulator", clean: true }, null, 2));
