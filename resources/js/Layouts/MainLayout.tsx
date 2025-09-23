import ApplicationLogo from "@/Components/ApplicationLogo";
import SearchBar from "@/Components/SearchBar";
import Footer from "@/Components/Footer";
import { useI18n } from "@/hooks/use-i18n";
import { Link, router, usePage } from "@inertiajs/react";
import {
    PropsWithChildren,
    ReactNode,
    useRef,
    useState,
    useEffect,
} from "react";
import { useIsomorphicLayoutEffect } from "@/hooks/use-isomorphic-layout-effect";

// Import our custom components
import DesktopNav from "@/Components/DesktopNav";
import MobileBottomNav from "@/Components/MobileBottomNav";
import MobileNav from "@/Components/MobileNav";
import UserActions from "@/Components/UserActions";
import { MaintenanceWrapper } from "@/Components/Settings/SettingsComponents";
import { App } from "@/types";
import LogoAnimation from "@/Components/LogoAnimation";

export default function MainLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const {
        auth,
        categories,
        brands,
        cartInfo: cart,
    } = usePage<App.Interfaces.AppPageProps>().props;
    const { direction, t, getLocalizedField } = useI18n();
    const user = auth?.user;

    const [isSearchOpen, setIsSearchOpen] = useState(false);
    const cartItemsCount = cart.itemsCount; // This would be replaced with actual cart data from backend
    const handleSearchClick = () => {
        setIsSearchOpen(!isSearchOpen);
    };

    const section = useRef<HTMLDivElement>(null);
    const animationInjection = useRef<HTMLDivElement>(null);

    // Move DOM manipulation to useEffect to prevent hydration issues
    useEffect(() => {
        // Only run on client side
        if (typeof window === 'undefined') return;

        // const html = document.querySelector("html") as HTMLHtmlElement;
        // html.setAttribute("dir", "rtl");

        const logo = document.querySelector(
            ".loading-container"
        ) as HTMLElement;
        logo?.classList.add("disabled");
    }, []);

    useEffect(() => {
        // Only run on client side
        if (typeof window === 'undefined') return;

        const existingAnimation = document.getElementById(
            "section-logo-animation"
        );

        const handleRouterStart = (e: any) => {
            if (
                e.detail.visit.method !== "get" ||
                e.detail.visit.url.pathname === window.location.pathname ||
                e.detail.visit.only.length !== 0
            )
                return;
            section.current?.classList.remove("section-loaded");
            section.current?.classList.add("section-go-away");
            if (animationInjection.current && existingAnimation) {
                animationInjection.current.appendChild(existingAnimation);
                animationInjection.current.classList.remove("hidden");
                animationInjection.current.classList.add("block");
            }
        };

        const handleRouterFinish = (e: any) => {
            if (
                e.detail.visit.method !== "get" ||
                e.detail.visit.only.length !== 0
            )
                return;
            section.current?.classList.remove("section-go-away");
            section.current?.classList.add("section-loaded");
            if (animationInjection.current && existingAnimation) {
                animationInjection.current.classList.remove("block");
                animationInjection.current.classList.add("hidden");
            }
        };

        const handlePopState = () => {
            setTimeout(
                () =>
                    window.scrollTo({
                        top: window.history.state?.documentScrollPosition?.top || 0,
                        behavior: "smooth",
                    }),
                100
            );
        };

        const removeStartListener = router.on("start", handleRouterStart);
        const removeFinishListener = router.on("finish", handleRouterFinish);
        window.addEventListener("popstate", handlePopState);

        // Cleanup event listeners
        return () => {
            removeStartListener();
            removeFinishListener();
            window.removeEventListener("popstate", handlePopState);
        };
    }, []);

    return (
        <MaintenanceWrapper>
            <div
                className="flex min-h-screen flex-col bg-background"
                dir={direction}
            >
                {/* Desktop Navigation */}
                <nav className="sticky top-0 z-40 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="container flex h-16 gap-4 items-center px-4">
                        {/* Logo */}
                        <div className="flex items-center ltr:mr-4 rtl:ml-4">
                            <Link href="/">
                                <ApplicationLogo className="h-8 w-auto  rounded-xl" />
                            </Link>
                        </div>

                        {/* Desktop Nav Links */}
                        <DesktopNav brands={brands} categories={categories} />

                        {/* User Actions (Search, Wishlist, User/Login, Cart) */}
                        <UserActions
                            user={user}
                            cartItemsCount={cartItemsCount}
                            onSearchClick={handleSearchClick}
                        />

                        {/* Mobile Navigation Menu Button */}
                        <MobileNav brands={brands} categories={categories} />
                    </div>

                    {/* Search Bar Component */}
                    <SearchBar
                        isOpen={isSearchOpen}
                        onClose={() => setIsSearchOpen(false)}
                    />
                </nav>

                {/* Mobile Bottom Navigation */}
                <MobileBottomNav
                    cartItemsCount={cartItemsCount}
                    onSearchClick={handleSearchClick}
                />

                {/* Main Content */}
                {header && (
                    <header className="bg-white shadow">
                        <div className="container mx-auto px-4 py-6">
                            {header}
                        </div>
                    </header>
                )}

                <main className="">
                    <div
                        ref={animationInjection}
                        className="hidden w-[200px] h-[200px] fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50"
                    ></div>
                    <div ref={section} className="">
                        {children}
                    </div>
                </main>

                {/* Footer */}
                <Footer />
            </div>
        </MaintenanceWrapper>
    );
}
