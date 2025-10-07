import { useSettings } from "@/hooks/useSettings";
import React, { useEffect } from "react";
import ReactPixel from "react-facebook-pixel";

export default function InitPixel({
    fbID,
    children,
}: {
    fbID?: string;
    children: React.ReactNode;
}) {
    useEffect(() => {
        if (!fbID) return;
        ReactPixel.init(fbID, undefined, { autoConfig: true, debug: false });
    }, []);
    return <>{children}</>;
}
