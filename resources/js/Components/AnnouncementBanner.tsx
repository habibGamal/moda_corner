import { useI18n } from '@/hooks/use-i18n';
import { useEffect, useState } from 'react';

interface AnnouncementBannerProps {
  announcements: { id: number; title_en: string; title_ar: string; }[];
}

export default function AnnouncementBanner({ announcements }: AnnouncementBannerProps) {
  const { getLocalizedField } = useI18n();
  const [currentAnnouncementIndex, setCurrentAnnouncementIndex] = useState(0);

  // Auto-rotate announcements
  useEffect(() => {
    if (!announcements || announcements.length === 0) return;

    const interval = setInterval(() => {
      setCurrentAnnouncementIndex(prev =>
        prev === announcements.length - 1 ? 0 : prev + 1
      );
    }, 5000);

    return () => clearInterval(interval);
  }, [announcements]);

  if (!announcements || announcements.length === 0) {
    return null;
  }

  return (
    <div className="bg-primary text-primary-foreground py-2 relative overflow-hidden">
      <div className="container mx-auto px-4 flex justify-center items-center animate-fadeIn">
        <p className="text-center font-medium">
          {getLocalizedField(announcements[currentAnnouncementIndex], 'title')}
        </p>
      </div>
    </div>
  );
}
