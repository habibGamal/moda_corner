import PolicyPage from '@/Components/PolicyPage';

interface ExchangePolicyProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function ExchangePolicy({ content, title }: ExchangePolicyProps) {
    return <PolicyPage content={content} title={title} />;
}
