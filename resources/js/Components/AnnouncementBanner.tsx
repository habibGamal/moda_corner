import { useI18n } from '@/hooks/use-i18n';
import { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Megaphone, Sparkles, X } from 'lucide-react';

interface AnnouncementBannerProps {
  announcements: { id: number; title_en: string; title_ar: string; }[];
}

export default function AnnouncementBanner({ announcements }: AnnouncementBannerProps) {
  const { getLocalizedField } = useI18n();
  const [currentAnnouncementIndex, setCurrentAnnouncementIndex] = useState(0);
  const [isVisible, setIsVisible] = useState(true);

  // Auto-rotate announcements
  useEffect(() => {
    if (!announcements || announcements.length === 0) return;

    const interval = setInterval(() => {
      setCurrentAnnouncementIndex(prev =>
        prev === announcements.length - 1 ? 0 : prev + 1
      );
    }, 6000);

    return () => clearInterval(interval);
  }, [announcements]);

  if (!announcements || announcements.length === 0 || !isVisible) {
    return null;
  }

  const currentAnnouncement = announcements[currentAnnouncementIndex];

  return (
    <AnimatePresence>
      {isVisible && (
        <motion.div
          initial={{ height: 0, opacity: 0 }}
          animate={{ height: 'auto', opacity: 1 }}
          exit={{ height: 0, opacity: 0 }}
          transition={{ duration: 0.5, ease: "easeInOut" }}
          className="force-ltr relative rounded-xl overflow-hidden bg-gradient-to-r from-primary-500 via-primary-600 to-primary-500 bg-size-200 animate-gradient-x"
        >
          {/* Animated Background Elements */}
          <div className="absolute inset-0 overflow-hidden">
            {/* Floating particles */}
            <motion.div
              className="absolute top-2 left-1/4 w-1 h-1 bg-primary-foreground/30 rounded-full"
              animate={{
                y: [0, -8, 0],
                opacity: [0.3, 0.8, 0.3],
                scale: [1, 1.2, 1]
              }}
              transition={{
                duration: 3,
                repeat: Infinity,
                ease: "easeInOut"
              }}
            />
            <motion.div
              className="absolute top-3 right-1/3 w-1.5 h-1.5 bg-primary-foreground/40 rounded-full"
              animate={{
                y: [0, -10, 0],
                opacity: [0.4, 0.9, 0.4],
                scale: [1, 1.3, 1]
              }}
              transition={{
                duration: 4,
                repeat: Infinity,
                ease: "easeInOut",
                delay: 1
              }}
            />
            <motion.div
              className="absolute bottom-2 left-1/3 w-1 h-1 bg-primary-foreground/25 rounded-full"
              animate={{
                y: [0, -6, 0],
                opacity: [0.25, 0.7, 0.25],
                scale: [1, 1.1, 1]
              }}
              transition={{
                duration: 2.5,
                repeat: Infinity,
                ease: "easeInOut",
                delay: 2
              }}
            />

            {/* Subtle glow effect */}
            <div className="absolute inset-0 bg-gradient-to-r from-transparent via-primary-foreground/8 to-transparent animate-pulse" />
          </div>

          <div className="container mx-auto px-4 py-3 relative">
            <div className="flex items-center justify-between">
              {/* Left Icon */}
              <motion.div
                initial={{ scale: 0, rotate: -180 }}
                animate={{ scale: 1, rotate: 0 }}
                transition={{
                  duration: 0.6,
                  type: "spring",
                  stiffness: 200,
                  damping: 15
                }}
                className="flex items-center"
              >
                <motion.div
                  animate={{
                    rotate: [0, 5, -5, 0],
                    scale: [1, 1.1, 1]
                  }}
                  transition={{
                    duration: 2,
                    repeat: Infinity,
                    ease: "easeInOut"
                  }}
                >
                  <Megaphone className="h-5 w-5 text-primary-foreground/95 mr-3" />
                </motion.div>
              </motion.div>

              {/* Center Content */}
              <div className="flex-1 flex items-center justify-center">
                <motion.div
                  key={currentAnnouncement.id}
                  initial={{
                    opacity: 0,
                    y: 20,
                    scale: 0.9,
                    rotateX: 30
                  }}
                  animate={{
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    rotateX: 0
                  }}
                  exit={{
                    opacity: 0,
                    y: -20,
                    scale: 1.1,
                    rotateX: -30
                  }}
                  transition={{
                    duration: 0.7,
                    type: "spring",
                    stiffness: 120,
                    damping: 12
                  }}
                  className="text-center"
                >
                  <motion.p
                    className="text-primary-foreground font-semibold text-sm md:text-base tracking-wide"
                    animate={{
                      textShadow: [
                        "0 0 0px rgba(255,255,255,0)",
                        "0 0 8px rgba(255,255,255,0.12)",
                        "0 0 0px rgba(255,255,255,0)"
                      ]
                    }}
                    transition={{
                      duration: 3,
                      repeat: Infinity,
                      ease: "easeInOut"
                    }}
                  >
                    {getLocalizedField(currentAnnouncement, 'title')}
                  </motion.p>
                </motion.div>
              </div>

              {/* Right Icons */}
              <div className="flex items-center space-x-2">
                {/* Sparkles Icon */}
                <motion.div
                  animate={{
                    rotate: [0, 360],
                    scale: [1, 1.2, 1]
                  }}
                  transition={{
                    duration: 4,
                    repeat: Infinity,
                    ease: "linear"
                  }}
                >
                  <Sparkles className="h-4 w-4 text-primary-200/80" />
                </motion.div>
              </div>
            </div>

            {/* Progress Indicators */}
            {announcements.length > 1 && (
              <motion.div
                className="flex justify-center space-x-2 mt-2"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.8 }}
              >
                {announcements.map((_, index) => (
                  <motion.button
                    key={index}
                    className={`h-1 rounded-full transition-all duration-300 cursor-pointer ${
                      index === currentAnnouncementIndex
                        ? 'w-8 bg-primary-foreground/90 shadow-sm shadow-primary-foreground/20'
                        : 'w-3 bg-primary-foreground/40 hover:bg-primary-foreground/60'
                    }`}
                    onClick={() => setCurrentAnnouncementIndex(index)}
                    whileHover={{
                      scale: 1.3,
                      y: -1,
                      backgroundColor: "rgba(255, 255, 255, 0.8)"
                    }}
                    whileTap={{ scale: 0.8 }}
                    animate={{
                      scale: index === currentAnnouncementIndex ? [1, 1.2, 1] : 1
                    }}
                    transition={{
                      duration: 0.5,
                      repeat: index === currentAnnouncementIndex ? Infinity : 0,
                      repeatDelay: 2
                    }}
                    aria-label={`Go to announcement ${index + 1}`}
                  />
                ))}
              </motion.div>
            )}
          </div>

          {/* Subtle border glow */}
          <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent" />
        </motion.div>
      )}
    </AnimatePresence>
  );
}
