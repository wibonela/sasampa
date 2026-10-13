// Renders every project in vertical and wide, each one passing the clean-standard check first.
import { execFileSync } from "node:child_process";
import { readdirSync } from "node:fs";

const projects = readdirSync(new URL("../data/", import.meta.url))
  .filter((f) => f.endsWith(".json") && !["style.json", "available.json"].includes(f))
  .map((f) => f.replace(".json", ""));
for (const p of projects) {
  for (const variant of ["", "wide"]) {
    execFileSync("node", ["scripts/render.mjs", p, ...(variant ? [variant] : [])], { stdio: "inherit" });
  }
}
