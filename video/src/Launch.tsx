import React from "react";
import {
  AbsoluteFill,
  Audio,
  Img,
  OffthreadVideo,
  Sequence,
  interpolate,
  spring,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { Beat, clipFor, config } from "./data";

const { brand } = config;
const FONT = "-apple-system, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif";
const PHONE_RATIO = 1206 / 2622;
const FADE = 10;

/** Fade a scene in and out so beats blend into each other. */
const useSceneFade = (duration: number) => {
  const frame = useCurrentFrame();
  return interpolate(frame, [0, FADE, duration - FADE, duration], [0, 1, 1, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
};

const Background: React.FC = () => {
  const frame = useCurrentFrame();
  const { width, height } = useVideoConfig();
  const t = frame / 90;
  const blob = (color: string, x: number, y: number, size: number, phase: number) => (
    <div
      style={{
        position: "absolute",
        left: x + Math.sin(t + phase) * 80,
        top: y + Math.cos(t * 0.8 + phase) * 80,
        width: size,
        height: size,
        borderRadius: "50%",
        background: color,
        filter: "blur(140px)",
        opacity: 0.45,
      }}
    />
  );
  return (
    <AbsoluteFill style={{ background: brand.dark, overflow: "hidden" }}>
      {blob(brand.accent, -width * 0.2, height * 0.05, width * 0.8, 0)}
      {blob(brand.blue, width * 0.45, height * 0.55, width * 0.9, 2)}
    </AbsoluteFill>
  );
};

const Progress: React.FC<{ index: number; total: number }> = ({ index, total }) => {
  const { width } = useVideoConfig();
  const gap = 10;
  const w = (Math.min(width, 1080) - 120 - gap * (total - 1)) / total;
  return (
    <div style={{ position: "absolute", top: 70, left: 60, display: "flex", gap }}>
      {Array.from({ length: total }).map((_, i) => (
        <div
          key={i}
          style={{
            width: w,
            height: 8,
            borderRadius: 4,
            background: i <= index ? brand.accent : "rgba(255,255,255,0.22)",
          }}
        />
      ))}
    </div>
  );
};

const Phone: React.FC<{ id: string; height: number; duration: number }> = ({ id, height, duration }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const enter = spring({ frame, fps, config: { damping: 18, stiffness: 90 } });
  const zoom = interpolate(frame, [0, duration], [1, 1.05]);
  const width = height * PHONE_RATIO;
  const bezel = 16;
  const clip = clipFor(id);
  return (
    <div
      style={{
        width: width + bezel * 2,
        height: height + bezel * 2,
        borderRadius: 96,
        background: "#05070D",
        padding: bezel,
        boxShadow: "0 50px 120px rgba(0,0,0,0.6), 0 0 0 3px rgba(255,255,255,0.12)",
        transform: `translateY(${interpolate(enter, [0, 1], [260, 0]) + Math.sin(frame / 40) * 8}px) scale(${interpolate(enter, [0, 1], [0.92, 1])})`,
        opacity: enter,
      }}
    >
      <div style={{ width, height, borderRadius: 80, overflow: "hidden", background: "#fff", position: "relative" }}>
        {clip ? (
          <OffthreadVideo
            src={staticFile(`clips/${clip}`)}
            style={{ width: "100%", height: "100%", objectFit: "cover", transform: `scale(${zoom})` }}
          />
        ) : (
          <AbsoluteFill style={{ alignItems: "center", justifyContent: "center", fontFamily: FONT, color: "#667", fontSize: 38, textAlign: "center", padding: 40 }}>
            Weka clip hapa
            <br />
            public/clips/{id}.mp4
          </AbsoluteFill>
        )}
      </div>
    </div>
  );
};

const Caption: React.FC<{ text: string; step: number; wide: boolean }> = ({ text, step, wide }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const pop = spring({ frame: frame - 4, fps, config: { damping: 200 } });
  return (
    <div
      style={{
        opacity: pop,
        transform: `translateY(${interpolate(pop, [0, 1], [30, 0])}px)`,
        fontFamily: FONT,
        color: brand.text,
        textAlign: wide ? "left" : "center",
        maxWidth: wide ? 760 : 940,
      }}
    >
      <div
        style={{
          display: "inline-block",
          fontSize: 34,
          fontWeight: 800,
          letterSpacing: 2,
          color: brand.accent,
          background: "rgba(255,59,48,0.14)",
          padding: "8px 22px",
          borderRadius: 999,
          marginBottom: 22,
        }}
      >
        {String(step).padStart(2, "0")}
      </div>
      <div style={{ fontSize: wide ? 78 : 70, fontWeight: 800, lineHeight: 1.1 }}>{text}</div>
    </div>
  );
};

const BeatScene: React.FC<{ beat: Beat; index: number; total: number; duration: number }> = ({ beat, index, total, duration }) => {
  const { width, height } = useVideoConfig();
  const opacity = useSceneFade(duration);
  const wide = width > height;
  return (
    <AbsoluteFill style={{ opacity }}>
      {!wide && <Progress index={index} total={total} />}
      {wide ? (
        <AbsoluteFill style={{ flexDirection: "row", alignItems: "center", justifyContent: "center", gap: 110, padding: "0 120px" }}>
          <Caption text={beat.caption} step={index + 1} wide />
          <Phone id={beat.id} height={height - 120} duration={duration} />
        </AbsoluteFill>
      ) : (
        <AbsoluteFill style={{ alignItems: "center", paddingTop: 150 }}>
          <Caption text={beat.caption} step={index + 1} wide={false} />
          <div style={{ position: "absolute", bottom: 70 }}>
            <Phone id={beat.id} height={1330} duration={duration} />
          </div>
        </AbsoluteFill>
      )}
    </AbsoluteFill>
  );
};

const Intro: React.FC<{ duration: number }> = ({ duration }) => {
  const frame = useCurrentFrame();
  const { fps, width } = useVideoConfig();
  const opacity = useSceneFade(duration);
  const a = spring({ frame, fps, config: { damping: 200 } });
  const strike = interpolate(frame, [fps * 1.3, fps * 1.9], [0, 100], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const b = spring({ frame: frame - fps * 2.2, fps, config: { damping: 14 } });
  return (
    <AbsoluteFill style={{ opacity, alignItems: "center", justifyContent: "center", fontFamily: FONT, color: brand.text, textAlign: "center", padding: 80 }}>
      <div style={{ fontSize: 190, transform: `scale(${a}) rotate(${interpolate(a, [0, 1], [-20, -4])}deg)` }}>📒</div>
      <div style={{ position: "relative", fontSize: width > 1500 ? 84 : 76, fontWeight: 800, lineHeight: 1.15, marginTop: 30, opacity: a, maxWidth: 1200 }}>
        {config.intro.line1}
        <div style={{ position: "absolute", left: 0, top: "52%", height: 10, width: `${strike}%`, background: brand.accent, borderRadius: 5 }} />
      </div>
      <div style={{ fontSize: 80, fontWeight: 800, marginTop: 50, color: brand.accent, opacity: Math.max(0, b), transform: `translateY(${interpolate(Math.max(0, b), [0, 1], [30, 0])}px)` }}>
        {config.intro.line2}
      </div>
    </AbsoluteFill>
  );
};

const EndCard: React.FC<{ duration: number }> = ({ duration }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const s = spring({ frame, fps, config: { damping: 12 } });
  const t = spring({ frame: frame - 12, fps, config: { damping: 200 } });
  const opacity = interpolate(frame, [0, FADE], [0, 1], { extrapolateRight: "clamp" });
  return (
    <AbsoluteFill style={{ opacity, alignItems: "center", justifyContent: "center", fontFamily: FONT, color: brand.text, textAlign: "center" }}>
      <Img src={staticFile("logo.png")} style={{ width: 300, height: 300, borderRadius: 72, transform: `scale(${s})`, boxShadow: "0 30px 80px rgba(255,59,48,0.45)" }} />
      <div style={{ fontSize: 108, fontWeight: 900, marginTop: 56, opacity: t, transform: `translateY(${interpolate(t, [0, 1], [30, 0])}px)` }}>{config.endCard.title}</div>
      <div style={{ fontSize: 66, marginTop: 20, color: brand.accent, fontWeight: 800, opacity: t }}>{config.endCard.subtitle}</div>
      {config.endCard.link ? <div style={{ fontSize: 46, marginTop: 44, opacity: 0.85 }}>{config.endCard.link}</div> : null}
    </AbsoluteFill>
  );
};

export const Launch: React.FC = () => {
  const { fps } = useVideoConfig();
  const introFrames = Math.round(config.intro.seconds * fps);
  let cursor = introFrames;
  return (
    <AbsoluteFill>
      <Background />
      {config.music ? <Audio src={staticFile(config.music)} volume={0.35} /> : null}
      <Sequence from={0} durationInFrames={introFrames}>
        <Intro duration={introFrames} />
      </Sequence>
      {config.beats.map((beat, i) => {
        const from = cursor;
        const duration = Math.round(beat.seconds * fps);
        cursor += duration;
        return (
          <Sequence key={beat.id} from={from} durationInFrames={duration}>
            <BeatScene beat={beat} index={i} total={config.beats.length} duration={duration} />
          </Sequence>
        );
      })}
      <Sequence from={cursor} durationInFrames={Math.round(config.endCard.seconds * fps)}>
        <EndCard duration={Math.round(config.endCard.seconds * fps)} />
      </Sequence>
    </AbsoluteFill>
  );
};
