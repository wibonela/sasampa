import { existsSync } from "node:fs";
import { Config } from "@remotion/cli/config";

Config.setVideoImageFormat("jpeg");
Config.setOverwriteOutput(true);

// Remotion's bundled headless browser crashes on this Mac; fall back to the installed Chrome.
const chrome = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";
if (existsSync(chrome)) {
  Config.setBrowserExecutable(chrome);
  Config.setChromeMode("chrome-for-testing");
}
