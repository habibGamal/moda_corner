import { Link } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Heart, Search, ShoppingCart, User } from "lucide-react";
import LanguageSwitcher from "@/Components/LanguageSwitcher";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";

interface UserActionsProps {
  user?: App.Models.User | null;
  cartItemsCount?: number;
  onSearchClick: () => void;
}

export default function UserActions({ user, cartItemsCount = 0, onSearchClick }: UserActionsProps) {
  const { t } = useI18n();

  return (
    <div className="flex items-center gap-2 ltr:ml-auto rtl:mr-auto">
        {/* Language Switcher */}
        <LanguageSwitcher className="hidden sm:flex" />

        {/* Search Button */}
        <Button
            variant="ghost"
            size="icon"
            onClick={onSearchClick}
            aria-label={t('search', 'Search')}
            className="hidden sm:flex"
        >
            <Search className="h-5 w-5" />
        </Button>

        {/* Wishlist Button */}
        <Button
            variant="ghost"
            size="icon"
            asChild
            aria-label={t('wishlist', 'Wishlist')}
        >
            <Link href="/wishlist">
                <Heart className="h-5 w-5" />
            </Link>
        </Button>

        {/* User/Login Menu */}
        {user ? (
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="rounded-full">
                        <Avatar className="h-8 w-8">
                            <AvatarImage src={user.avatar || ''} alt={user.name} />
                            <AvatarFallback>{user.name?.charAt(0) || 'U'}</AvatarFallback>
                        </Avatar>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem asChild>
                        <Link href={route('profile.edit')}>{t('profile', 'Profile')}</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href="/orders">{t('my_orders', 'My Orders')}</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={route('logout')} method="post" as="button" className="w-full text-left">
                            {t('log_out', 'Logout')}
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        ) : (
            <Button variant="ghost" size="icon" asChild>
                <Link href={route('login')}>
                    <User className="h-5 w-5" />
                </Link>
            </Button>
        )}

        {/* Cart Button with Item Count */}
        <Button
            variant="ghost"
            size="icon"
            asChild
            aria-label={t('cart', 'Cart')}
            className="hidden sm:flex"
        >
            <Link href="/cart">
                <div className="relative">
                    <ShoppingCart className="h-5 w-5" />
                    {cartItemsCount > 0 && (
                        <span className="absolute flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] text-primary-foreground ltr:-right-2 rtl:-left-2 -top-2">
                            {cartItemsCount > 9 ? '9+' : cartItemsCount}
                        </span>
                    )}
                </div>
            </Link>
        </Button>
    </div>
  );
}
