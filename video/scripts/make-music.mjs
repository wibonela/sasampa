// Synthesises an original, royalty-free background track (soft pads, pluck arpeggio, light beat).
//   node scripts/make-music.mjs   ->  public/audio/music.m4a
// Replace public/audio/music.m4a with your own licensed track at any time; the edit uses whatever file is there.
import { execFileSync } from "node:child_process";
import { mkdirSync, mkdtempSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";

const SR = 44100;
const BPM = 100;
const BAR = (60 / BPM) * 4;
const BARS = 44;
const N = Math.round(SR * BAR * BARS);
const L = new Float32Array(N), R = new Float32Array(N);

const hz = (m) => 440 * 2 ** ((m - 69) / 12);
// Am - F - C - G, as MIDI notes (pad voicing) and bass roots
const chords = [
  { pad: [57, 60, 64], bass: 45 },
  { pad: [53, 57, 60], bass: 41 },
  { pad: [55, 60, 64], bass: 48 },
  { pad: [55, 59, 62], bass: 43 },
];

let seed = 1;
const rnd = () => ((seed = (seed * 1664525 + 1013904223) >>> 0) / 4294967296) * 2 - 1;
const add = (i, l, r = l) => { if (i >= 0 && i < N) { L[i] += l; R[i] += r; } };

for (let bar = 0; bar < BARS; bar++) {
  const c = chords[bar % 4];
  const t0 = Math.round(bar * BAR * SR);
  const len = Math.round(BAR * SR);

  // pad: two slightly detuned sines per note, soft attack/release overlapping the next bar
  for (const [k, m] of c.pad.entries()) {
    for (const det of [-0.0035, 0.0035]) {
      const f = hz(m) * (1 + det);
      for (let i = 0; i < len + 4000; i++) {
        const x = i / SR;
        const env = Math.min(1, x / 0.6) * Math.min(1, (len + 4000 - i) / 4000);
        const s = Math.sin(2 * Math.PI * f * x) * 0.05 * env;
        add(t0 + i, s * (k % 2 ? 0.8 : 1.1), s * (k % 2 ? 1.1 : 0.8));
      }
    }
  }
  // bass on beats 1 and 3
  for (const beat of [0, 2]) {
    const s0 = t0 + Math.round(beat * (BAR / 4) * SR);
    for (let i = 0; i < SR * 0.9; i++) {
      const x = i / SR;
      add(s0 + i, Math.sin(2 * Math.PI * hz(c.bass) * x) * 0.16 * Math.exp(-x * 3.2) * Math.min(1, x / 0.01));
    }
  }
  // pluck arpeggio on eighth notes
  const arp = [c.pad[0] + 12, c.pad[1] + 12, c.pad[2] + 12, c.pad[1] + 12, c.pad[2] + 12, c.pad[1] + 12, c.pad[0] + 12, c.pad[1] + 12];
  arp.forEach((m, j) => {
    const s0 = t0 + Math.round(j * (BAR / 8) * SR);
    const pan = 0.5 + 0.35 * Math.sin(j + bar);
    for (let i = 0; i < SR * 0.5; i++) {
      const x = i / SR;
      const v = (Math.sin(2 * Math.PI * hz(m) * x) + 0.3 * Math.sin(4 * Math.PI * hz(m) * x)) * 0.07 * Math.exp(-x * 9) * Math.min(1, x / 0.004);
      add(s0 + i, v * (1 - pan) * 1.6, v * pan * 1.6);
    }
  });
  // kick on 1 and 3 (from bar 2), hat on off-beats (from bar 4)
  if (bar >= 2) for (const beat of [0, 2]) {
    const s0 = t0 + Math.round(beat * (BAR / 4) * SR);
    let ph = 0;
    for (let i = 0; i < SR * 0.25; i++) {
      const x = i / SR;
      ph += 2 * Math.PI * (45 + 90 * Math.exp(-x * 28)) / SR;
      add(s0 + i, Math.sin(ph) * 0.22 * Math.exp(-x * 14));
    }
  }
  if (bar >= 4) for (let j = 1; j < 8; j += 2) {
    const s0 = t0 + Math.round(j * (BAR / 8) * SR);
    for (let i = 0; i < SR * 0.05; i++) add(s0 + i, rnd() * 0.025 * Math.exp(-(i / SR) * 70));
  }
}

// 16-bit stereo WAV
const buf = Buffer.alloc(44 + N * 4);
buf.write("RIFF", 0); buf.writeUInt32LE(36 + N * 4, 4); buf.write("WAVEfmt ", 8);
buf.writeUInt32LE(16, 16); buf.writeUInt16LE(1, 20); buf.writeUInt16LE(2, 22);
buf.writeUInt32LE(SR, 24); buf.writeUInt32LE(SR * 4, 28); buf.writeUInt16LE(4, 32); buf.writeUInt16LE(16, 34);
buf.write("data", 36); buf.writeUInt32LE(N * 4, 40);
let peak = 0;
for (let i = 0; i < N; i++) peak = Math.max(peak, Math.abs(L[i]), Math.abs(R[i]));
peak ||= 1;
for (let i = 0; i < N; i++) {
  buf.writeInt16LE(Math.round((L[i] / peak) * 0.85 * 32767), 44 + i * 4);
  buf.writeInt16LE(Math.round((R[i] / peak) * 0.85 * 32767), 46 + i * 4);
}
const tmp = join(mkdtempSync(join(tmpdir(), "music-")), "music.wav");
writeFileSync(tmp, buf);

const out = new URL("../public/audio/music.m4a", import.meta.url).pathname;
mkdirSync(new URL("../public/audio/", import.meta.url).pathname, { recursive: true });
execFileSync("ffmpeg", ["-v", "error", "-y", "-i", tmp, "-af", "aecho=0.8:0.6:60|120:0.25|0.15,lowpass=f=9000,loudnorm=I=-18:TP=-2", "-c:a", "aac", "-b:a", "160k", out]);
console.log(`music.m4a: ${(N / SR).toFixed(0)}s`);
