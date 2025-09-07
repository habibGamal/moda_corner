import PolicyPage from '@/Components/PolicyPage';

interface TermsProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function Terms({ content, title }: TermsProps) {
    return <PolicyPage content={content} title={title} />;
}
