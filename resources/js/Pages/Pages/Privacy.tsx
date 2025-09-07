import PolicyPage from '@/Components/PolicyPage';

interface PrivacyProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function Privacy({ content, title }: PrivacyProps) {
    return <PolicyPage content={content} title={title} />;
}
