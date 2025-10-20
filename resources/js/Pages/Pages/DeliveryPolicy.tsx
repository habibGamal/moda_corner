import PolicyPage from '@/Components/PolicyPage';

interface DeliveryPolicyProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function DeliveryPolicy({ content, title }: DeliveryPolicyProps) {
    return <PolicyPage content={content} title={title} />;
}
