import React from "react";
import { Composition } from "remotion";
import { Launch } from "./Launch";
import { config, totalFrames } from "./data";

export const Root: React.FC = () => (
  <>
    <Composition id="Launch" component={Launch} durationInFrames={totalFrames()} fps={config.fps} width={1080} height={1920} />
    <Composition id="LaunchWide" component={Launch} durationInFrames={totalFrames()} fps={config.fps} width={1920} height={1080} />
  </>
);
