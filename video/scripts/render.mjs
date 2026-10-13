// Runs the clean-standard check, then renders. Usage: node scripts/render.mjs <project> [wide]
import { execFileSync } from "node:child_process";
import { mkdirSync } from "node:fs";

const [project, variant] = process.argv.slice(2);
if (!project) throw new Error("usage: render.mjs <project> [wide]");
const wide = variant === "wide";
const run = (cmd, args) => execFileSync(cmd, args, { stdio: "inherit" });

run("node", ["scripts/sync-clips.mjs"]);
run("node", ["scripts/check.mjs", project]); // exits non-zero (and stops here) if the standard is not met
mkdirSync("out", { recursive: true });
run("npx", ["remotion", "render", "src/index.ts", wide ? `${project}-wide` : project, `out/${project}-${wide ? "16x9" : "9x16"}.mp4`]);
