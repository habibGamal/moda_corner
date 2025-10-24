import PolicyPage from '@/Components/PolicyPage';

interface ShippingPolicyProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function ShippingPolicy({ content, title }: ShippingPolicyProps) {
    return <PolicyPage content={content} title={title} />;
}
