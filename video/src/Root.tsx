import React from "react";
import { Composition } from "remotion";
import { Video } from "./Video";
import { projects, totalFrames } from "./data";

// Every project gets a vertical (9:16) and a wide (16:9) composition: "<project>" and "<project>-wide".
export const Root: React.FC = () => (
  <>
    {Object.entries(projects).flatMap(([id, p]) => [
      <Composition key={id} id={id} component={Video} defaultProps={{ project: id }} durationInFrames={totalFrames(p)} fps={p.fps} width={1080} height={1920} />,
      <Composition key={`${id}-wide`} id={`${id}-wide`} component={Video} defaultProps={{ project: id }} durationInFrames={totalFrames(p)} fps={p.fps} width={1920} height={1080} />,
    ])}
  </>
);
