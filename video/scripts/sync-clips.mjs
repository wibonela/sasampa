// Lists the clips present in public/clips so the video shows a placeholder for any that are missing.
import { readdirSync, writeFileSync, existsSync } from "node:fs";

const dir = new URL("../public/clips/", import.meta.url);
const files = existsSync(dir)
  ? readdirSync(dir).filter((f) => /\.(mp4|mov|webm)$/i.test(f))
  : [];
writeFileSync(new URL("../data/available.json", import.meta.url), JSON.stringify(files, null, 2));
console.log(`Found ${files.length} clip(s):`, files.join(", ") || "none (placeholders will be used)");
