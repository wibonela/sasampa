import launch from "../data/launch.json";
import tutorialSale from "../data/tutorial-sale.json";
import available from "../data/available.json";

export type Beat = { id: string; seconds: number; caption: string; vo?: string; speed?: number };
export type Project = {
  project: string;
  fps: number;
  voiceover: boolean;
  music: { file: string; volume: number; volumeUnderVoice: number } | null;
  intro: { seconds: number; emoji: string; strike: boolean; line1: string; line2: string; vo?: string };
  beats: Beat[];
  endCard: { seconds: number; title: string; subtitle: string; link: string; vo?: string };
};

export const projects: Record<string, Project> = {
  launch: launch as Project,
  "tutorial-sale": tutorialSale as Project,
};

type Available = { clips: Record<string, string[]>; audio: Record<string, string[]>; music: string[] };
const files = available as Available;

export const totalFrames = (p: Project) =>
  Math.round((p.intro.seconds + p.beats.reduce((s, b) => s + b.seconds, 0) + p.endCard.seconds) * p.fps);

/** Finds the recorded clip for a beat by its id, regardless of extension. */
export const clipFor = (project: string, id: string) =>
  files.clips[project]?.find((f) => f.startsWith(id + "."));

/** Finds a generated voiceover file (beat id, "intro" or "end"). */
export const voiceFor = (project: string, id: string) =>
  files.audio[project]?.find((f) => f.startsWith(id + "."));

export const hasMusic = (file: string) => files.music.includes(file.replace(/^audio\//, ""));
