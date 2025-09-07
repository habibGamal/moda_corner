import { useEffect, useState } from "react";

const AnimatedBackground = () => {
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  if (!mounted) return null;

  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none">
      {/* Main Gradient Background */}
      <div className="absolute inset-0 bg-gradient-to-br from-emerald-100/30 via-transparent to-emerald-200/20" />

      {/* Swimming Fish Shapes - More distributed */}
      <div className="absolute top-20 left-10 w-16 h-8 bg-gradient-to-r from-emerald-300/40 to-emerald-400/30 rounded-full blur-sm animate-[swim_8s_ease-in-out_infinite] transform rotate-12" />
      <div className="absolute top-32 left-20 w-12 h-6 bg-gradient-to-r from-emerald-400/50 to-emerald-300/40 rounded-full blur-sm animate-[swim_6s_ease-in-out_infinite_reverse] transform -rotate-6" />
      <div className="absolute top-60 right-32 w-20 h-10 bg-gradient-to-r from-emerald-300/35 to-emerald-200/30 rounded-full blur-sm animate-[swim_10s_ease-in-out_infinite] transform rotate-45" />
      <div className="absolute bottom-40 left-16 w-14 h-7 bg-gradient-to-r from-emerald-400/45 to-emerald-300/35 rounded-full blur-sm animate-[swim_7s_ease-in-out_infinite_reverse] transform -rotate-12" />
      <div className="absolute bottom-60 right-20 w-18 h-9 bg-gradient-to-r from-emerald-300/40 to-emerald-400/30 rounded-full blur-sm animate-[swim_9s_ease-in-out_infinite] transform rotate-24" />
      <div className="absolute top-80 left-32 w-10 h-5 bg-gradient-to-r from-emerald-400/35 to-emerald-300/25 rounded-full blur-sm animate-[swim_5s_ease-in-out_infinite] transform rotate-30" />
      <div className="absolute bottom-80 right-40 w-22 h-11 bg-gradient-to-r from-emerald-300/30 to-emerald-400/20 rounded-full blur-sm animate-[swim_11s_ease-in-out_infinite_reverse] transform -rotate-15" />
      <div className="absolute top-96 right-16 w-15 h-8 bg-gradient-to-r from-emerald-400/40 to-emerald-300/30 rounded-full blur-sm animate-[swim_4s_ease-in-out_infinite] transform rotate-60" />

      {/* Wing-like Flowing Shapes - More mystical */}
      <div className="absolute top-16 right-16 w-40 h-20 bg-gradient-to-br from-emerald-300/25 to-emerald-400/15 rounded-[50%_30%_70%_40%] blur-lg animate-[float_12s_ease-in-out_infinite] transform rotate-45" />
      <div className="absolute bottom-32 left-8 w-36 h-18 bg-gradient-to-tl from-emerald-400/30 to-emerald-300/20 rounded-[40%_60%_30%_70%] blur-lg animate-[float_15s_ease-in-out_infinite_reverse] transform -rotate-30" />
      <div className="absolute top-40 left-1/3 w-32 h-16 bg-gradient-to-br from-emerald-300/22 to-emerald-400/12 rounded-[60%_40%_80%_20%] blur-lg animate-[float_18s_ease-in-out_infinite] transform rotate-75" />
      <div className="absolute bottom-48 right-1/3 w-28 h-14 bg-gradient-to-tl from-emerald-400/28 to-emerald-300/18 rounded-[30%_70%_50%_50%] blur-lg animate-[float_14s_ease-in-out_infinite_reverse] transform -rotate-45" />

      {/* Geometric Shapes - More mystical */}
      <div className="absolute top-32 right-32 w-16 h-16 border-2 border-emerald-300/30 rotate-45 animate-[spin_20s_linear_infinite]" />
      <div className="absolute bottom-32 left-32 w-12 h-12 border-2 border-emerald-400/25 rotate-12 animate-[spin_15s_linear_infinite_reverse]" />
      <div className="absolute top-64 left-1/4 w-20 h-20 border-2 border-emerald-300/20 rotate-30 animate-[spin_25s_linear_infinite]" />
      <div className="absolute bottom-64 right-1/4 w-14 h-14 border-2 border-emerald-400/35 rotate-60 animate-[spin_18s_linear_infinite_reverse]" />

      {/* Particle Effects - Enhanced */}
      <div className="absolute top-1/4 left-1/4 w-2 h-2 bg-emerald-400/50 rounded-full animate-[pulse_3s_ease-in-out_infinite]" />
      <div className="absolute top-3/4 right-1/4 w-1 h-1 bg-emerald-300/70 rounded-full animate-[pulse_4s_ease-in-out_infinite]" />
      <div className="absolute top-1/2 left-3/4 w-3 h-3 bg-emerald-400/40 rounded-full animate-[pulse_5s_ease-in-out_infinite]" />
      <div className="absolute top-1/3 right-1/3 w-2 h-2 bg-emerald-300/60 rounded-full animate-[pulse_6s_ease-in-out_infinite]" />
      <div className="absolute bottom-1/3 left-1/3 w-1 h-1 bg-emerald-400/70 rounded-full animate-[pulse_3.5s_ease-in-out_infinite]" />
      <div className="absolute top-2/3 left-1/5 w-2 h-2 bg-emerald-300/50 rounded-full animate-[pulse_4.5s_ease-in-out_infinite]" />

      {/* Animated Lines */}
      <svg className="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stopColor="rgb(52 211 153)" stopOpacity="0.2" />
            <stop offset="100%" stopColor="rgb(110 231 183)" stopOpacity="0.1" />
          </linearGradient>
        </defs>
        <path
          d="M 0,200 Q 400,100 800,300 T 1600,200"
          stroke="url(#lineGradient)"
          strokeWidth="2"
          fill="none"
          className="animate-[drawLine_8s_ease-in-out_infinite]"
        />
        <path
          d="M 0,400 Q 200,300 600,500 T 1600,400"
          stroke="url(#lineGradient)"
          strokeWidth="1"
          fill="none"
          className="animate-[drawLine_10s_ease-in-out_infinite_reverse]"
        />
      </svg>


      {/* Additional floating elements for richness */}
      <div className="absolute top-40 right-20 w-8 h-8 bg-emerald-300/20 rounded-full animate-[drift_12s_ease-in-out_infinite]" />
      <div className="absolute bottom-40 left-40 w-6 h-6 bg-emerald-400/25 rounded-full animate-[drift_15s_ease-in-out_infinite_reverse]" />
      <div className="absolute top-72 left-20 w-10 h-10 bg-emerald-300/15 rounded-full animate-[drift_18s_ease-in-out_infinite]" />

      {/* More geometric variety */}
      <div className="absolute top-48 right-48 w-12 h-12 border border-emerald-300/25 rounded-full animate-[spin_30s_linear_infinite]" />
      <div className="absolute bottom-48 left-48 w-8 h-8 border border-emerald-400/30 rounded-full animate-[spin_25s_linear_infinite_reverse]" />
    </div>
  );
};

export default AnimatedBackground;
