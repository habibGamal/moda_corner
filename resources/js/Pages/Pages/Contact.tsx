import PolicyPage from '@/Components/PolicyPage';

interface ContactProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function Contact({ content, title }: ContactProps) {
    return <PolicyPage content={content} title={title} />;
}
