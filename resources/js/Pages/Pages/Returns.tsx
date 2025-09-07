import PolicyPage from '@/Components/PolicyPage';

interface ReturnsProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function Returns({ content, title }: ReturnsProps) {
    return <PolicyPage content={content} title={title} />;
}
