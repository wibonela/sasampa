import launch from "../data/launch.json";
import available from "../data/available.json";

export type Beat = { id: string; seconds: number; caption: string };

export const config = launch as typeof launch & { beats: Beat[] };
export const clips: string[] = available;

export const totalFrames = () =>
  Math.round(
    (config.intro.seconds +
      config.beats.reduce((s, b) => s + b.seconds, 0) +
      config.endCard.seconds) *
      config.fps,
  );

/** Finds the recorded clip for a beat by its id, regardless of extension. */
export const clipFor = (id: string) => clips.find((f) => f.startsWith(id + "."));
