import PolicyPage from '@/Components/PolicyPage';

interface AboutUsProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function AboutUs({ content, title }: AboutUsProps) {
    return <PolicyPage content={content} title={title} />;
}
