import { jsx, jsxs, Fragment } from "react/jsx-runtime";
import { router, useForm, Head, Link, usePage, WhenVisible, createInertiaApp } from "@inertiajs/react";
import * as React from "react";
import React__default, { useEffect, useState, useCallback, useRef, useLayoutEffect } from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva } from "class-variance-authority";
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import * as LabelPrimitive from "@radix-ui/react-label";
import { useTranslation, initReactI18next } from "react-i18next";
import * as CheckboxPrimitive from "@radix-ui/react-checkbox";
import { Check, Facebook, Chrome, Award, ShoppingBag, Trash2, MinusCircle, PlusCircle, ArrowRight, ArrowLeft, Layers, FolderX, X, ChevronDown, ChevronUp, Receipt, Circle, CreditCard, Banknote, MapPin, Tag, Gift, CircleCheck, Percent, AlertTriangle, Truck, Plus, Megaphone, Sparkles, PackageOpen, LayoutGrid, RotateCcw, Package, CalendarDays, Clock, CheckCircle, AlertCircle, Calendar, Home as Home$2, Building, User, Mail, ExternalLink, Heart, ChevronLeft, ChevronRight, Minus, Languages, UserCog, FilterIcon, ArrowDown01, ArrowDown10, ArrowDownAZ, ArrowDownZA, Loader2, Search, Instagram, Linkedin, Youtube, Music2, ShoppingCart, Menu } from "lucide-react";
import axios from "axios";
import * as DialogPrimitive from "@radix-ui/react-dialog";
import { useFormContext, FormProvider, Controller, useForm as useForm$1 } from "react-hook-form";
import * as SelectPrimitive from "@radix-ui/react-select";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import * as RadioGroupPrimitive from "@radix-ui/react-radio-group";
import { AnimatePresence, motion } from "framer-motion";
import Autoplay from "embla-carousel-autoplay";
import useEmblaCarousel from "embla-carousel-react";
import * as ProgressPrimitive from "@radix-ui/react-progress";
import * as AvatarPrimitive from "@radix-ui/react-avatar";
import * as SeparatorPrimitive from "@radix-ui/react-separator";
import * as ScrollAreaPrimitive from "@radix-ui/react-scroll-area";
import * as DropdownMenuPrimitive from "@radix-ui/react-dropdown-menu";
import * as CollapsiblePrimitive from "@radix-ui/react-collapsible";
import createServer from "@inertiajs/react/server";
import ReactDOMServer from "react-dom/server";
import * as ToastPrimitives from "@radix-ui/react-toast";
import * as NavigationMenuPrimitive from "@radix-ui/react-navigation-menu";
import i18n from "i18next";
import LanguageDetector from "i18next-browser-languagedetector";
function cn(...inputs) {
  return twMerge(clsx(inputs));
}
function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  }).format(date);
}
const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground shadow hover:bg-primary/90",
        destructive: "bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90",
        outline: "border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground",
        secondary: "bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-primary underline-offset-4 hover:underline"
      },
      size: {
        default: "h-9 px-4 py-2",
        sm: "h-8 rounded-md px-3 text-xs",
        lg: "h-10 rounded-md px-8",
        icon: "h-9 w-9"
      }
    },
    defaultVariants: {
      variant: "default",
      size: "default"
    }
  }
);
const Button = React.forwardRef(
  ({ className, variant: variant2, size: size2, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button";
    return /* @__PURE__ */ jsx(
      Comp,
      {
        className: cn(buttonVariants({ variant: variant2, size: size2, className })),
        ref,
        ...props
      }
    );
  }
);
Button.displayName = "Button";
const Input = React.forwardRef(
  ({ className, type, ...props }, ref) => {
    return /* @__PURE__ */ jsx(
      "input",
      {
        type,
        className: cn(
          "flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
          className
        ),
        ref,
        ...props
      }
    );
  }
);
Input.displayName = "Input";
const labelVariants = cva(
  "text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
);
const Label = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  LabelPrimitive.Root,
  {
    ref,
    className: cn(labelVariants(), className),
    ...props
  }
));
Label.displayName = LabelPrimitive.Root.displayName;
const languages = [
  { code: "en", name: "English", direction: "ltr" },
  { code: "ar", name: "العربية", direction: "rtl" }
];
function useI18n() {
  const { t, i18n: i18n2 } = useTranslation();
  const currentLocale = i18n2.language;
  const direction = currentLocale === "ar" ? "rtl" : "ltr";
  const setLanguage = (langCode) => {
    i18n2.changeLanguage(langCode);
    const newDirection = langCode === "ar" ? "rtl" : "ltr";
    document.documentElement.dir = newDirection;
    document.documentElement.lang = langCode;
    localStorage.setItem("language", langCode);
    router.reload({
      only: ["translations"],
      data: { locale: langCode }
    });
  };
  const translate = (key, fallback, variables) => {
    const translation = t(key, {
      defaultValue: fallback || key,
      ...variables
    });
    return translation;
  };
  const getLocalizedField = (obj, fieldName) => {
    if (!obj) return void 0;
    const localizedFieldKey = `${fieldName}_${currentLocale}`;
    if (obj[localizedFieldKey] !== void 0) {
      return obj[localizedFieldKey];
    }
    const englishFieldKey = `${fieldName}_en`;
    return obj[englishFieldKey];
  };
  return {
    currentLocale,
    direction,
    languages,
    setLanguage,
    t: translate,
    getLocalizedField,
    // Expose the original i18n instance for advanced usage
    i18n: i18n2
  };
}
function ConfirmPassword() {
  const { t } = useI18n();
  const { data, setData, post, processing, errors, reset } = useForm({
    password: ""
  });
  const submit = (e) => {
    e.preventDefault();
    post(route("password.confirm"), {
      onFinish: () => reset("password")
    });
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("confirm_password", "Confirm Password") }),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-md space-y-6 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold tracking-tight", children: t("confirm_password", "Confirm Password") }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("secure_area_confirmation", "This is a secure area of the application. Please confirm your password before continuing.") })
      ] }),
      /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "password", children: t("password", "Password") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "password",
              type: "password",
              name: "password",
              value: data.password,
              autoComplete: "current-password",
              autoFocus: true,
              onChange: (e) => setData("password", e.target.value),
              className: errors.password ? "border-destructive" : ""
            }
          ),
          errors.password && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.password })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "flex justify-end", children: /* @__PURE__ */ jsx(Button, { type: "submit", disabled: processing, children: t("confirm", "Confirm") }) })
      ] })
    ] })
  ] });
}
const __vite_glob_0_0 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ConfirmPassword
}, Symbol.toStringTag, { value: "Module" }));
const alertVariants = cva(
  "relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground",
  {
    variants: {
      variant: {
        default: "bg-background text-foreground",
        destructive: "border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive",
        success: "border-green-500/50 text-green-600 dark:border-green-500 [&>svg]:text-green-600"
      }
    },
    defaultVariants: {
      variant: "default"
    }
  }
);
const Alert = React.forwardRef(({ className, variant: variant2, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    role: "alert",
    className: cn(alertVariants({ variant: variant2 }), className),
    ...props
  }
));
Alert.displayName = "Alert";
const AlertTitle = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "h5",
  {
    ref,
    className: cn("mb-1 font-medium leading-none tracking-tight", className),
    ...props
  }
));
AlertTitle.displayName = "AlertTitle";
const AlertDescription = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    className: cn("text-sm [&_p]:leading-relaxed", className),
    ...props
  }
));
AlertDescription.displayName = "AlertDescription";
function ForgotPassword({ status }) {
  const { t } = useI18n();
  const { data, setData, post, processing, errors } = useForm({
    email: ""
  });
  const submit = (e) => {
    e.preventDefault();
    post(route("password.email"));
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("forgot_password", "Forgot Password") }),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-md space-y-6 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold tracking-tight", children: t("forgot_password", "Forgot Password") }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("forgot_password_description", "Enter your email and we'll send you a link to reset your password.") })
      ] }),
      status && /* @__PURE__ */ jsx(Alert, { className: "my-6", children: /* @__PURE__ */ jsx(AlertDescription, { children: status }) }),
      /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "email", children: t("email", "Email") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "email",
              type: "email",
              name: "email",
              value: data.email,
              autoComplete: "username",
              autoFocus: true,
              onChange: (e) => setData("email", e.target.value),
              className: errors.email ? "border-destructive" : ""
            }
          ),
          errors.email && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.email })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
          /* @__PURE__ */ jsx(
            Link,
            {
              href: route("login"),
              className: "text-sm text-primary hover:underline",
              children: t("back_to_login", "Back to Login")
            }
          ),
          /* @__PURE__ */ jsx(
            Button,
            {
              type: "submit",
              disabled: processing,
              children: t("send_reset_link", "Email Password Reset Link")
            }
          )
        ] })
      ] })
    ] })
  ] });
}
const __vite_glob_0_1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ForgotPassword
}, Symbol.toStringTag, { value: "Module" }));
const Checkbox = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  CheckboxPrimitive.Root,
  {
    ref,
    className: cn(
      "peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground",
      className
    ),
    ...props,
    children: /* @__PURE__ */ jsx(
      CheckboxPrimitive.Indicator,
      {
        className: cn("flex items-center justify-center text-current"),
        children: /* @__PURE__ */ jsx(Check, { className: "h-4 w-4" })
      }
    )
  }
));
Checkbox.displayName = CheckboxPrimitive.Root.displayName;
function Login({
  status,
  canResetPassword
}) {
  const { t } = useI18n();
  const { data, setData, post, processing, errors, reset } = useForm({
    email: "",
    password: "",
    remember: false
  });
  const submit = (e) => {
    e.preventDefault();
    post(route("login"), {
      onFinish: () => reset("password")
    });
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("login", "Log in") }),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-md space-y-6 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold tracking-tight", children: t("login", "Log in") }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("login_description", "Enter your information to access your account") })
      ] }),
      status && /* @__PURE__ */ jsx(Alert, { variant: "success", className: "my-6", children: /* @__PURE__ */ jsx(AlertDescription, { children: status }) }),
      /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-4", children: [
        /* @__PURE__ */ jsx(
          Button,
          {
            asChild: true,
            className: "flex items-center justify-center gap-2 w-full bg-[#1877F3] text-white hover:bg-[#166fe0] focus:ring-2 focus:ring-blue-500",
            children: /* @__PURE__ */ jsxs("a", { href: route("social.login", { provider: "facebook" }), children: [
              /* @__PURE__ */ jsx(Facebook, { className: "h-4 w-4" }),
              "Facebook"
            ] })
          }
        ),
        /* @__PURE__ */ jsx(
          Button,
          {
            asChild: true,
            className: "flex items-center justify-center gap-2 w-full bg-[#DB4437] text-white hover:bg-[#c23321] focus:ring-2 focus:ring-red-500",
            children: /* @__PURE__ */ jsxs("a", { href: route("social.login", { provider: "google" }), children: [
              /* @__PURE__ */ jsx(Chrome, { className: "h-4 w-4" }),
              "Google"
            ] })
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "relative my-4", children: [
        /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center", children: /* @__PURE__ */ jsx("div", { className: "w-full border-t border-muted" }) }),
        /* @__PURE__ */ jsx("div", { className: "relative flex justify-center text-xs uppercase", children: /* @__PURE__ */ jsx("span", { className: "bg-background px-2 text-muted-foreground", children: t("or_continue_with", "Or continue with") }) })
      ] }),
      /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "email", children: t("email", "Email") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "email",
              type: "email",
              name: "email",
              value: data.email,
              autoComplete: "username",
              autoFocus: true,
              onChange: (e) => setData("email", e.target.value),
              className: errors.email ? "border-destructive" : ""
            }
          ),
          errors.email && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.email })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
            /* @__PURE__ */ jsx(Label, { htmlFor: "password", children: t("password", "Password") }),
            canResetPassword && /* @__PURE__ */ jsx(
              Link,
              {
                href: route("password.request"),
                className: "text-xs text-primary hover:underline",
                children: t("forgot_password", "Forgot your password?")
              }
            )
          ] }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "password",
              type: "password",
              name: "password",
              value: data.password,
              autoComplete: "current-password",
              onChange: (e) => setData("password", e.target.value),
              className: errors.password ? "border-destructive" : ""
            }
          ),
          errors.password && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.password })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center space-x-2 rtl:space-x-reverse", children: [
          /* @__PURE__ */ jsx(
            Checkbox,
            {
              id: "remember",
              name: "remember",
              checked: data.remember,
              onCheckedChange: (checked) => setData("remember", Boolean(checked))
            }
          ),
          /* @__PURE__ */ jsx(
            Label,
            {
              htmlFor: "remember",
              className: "text-sm font-normal",
              children: t("remember_me", "Remember me")
            }
          )
        ] }),
        /* @__PURE__ */ jsx(
          Button,
          {
            type: "submit",
            className: "w-full",
            disabled: processing,
            children: t("login", "Log in")
          }
        ),
        /* @__PURE__ */ jsxs("div", { className: "text-center text-sm", children: [
          /* @__PURE__ */ jsxs("span", { className: "text-muted-foreground", children: [
            t("dont_have_account", "Don't have an account?"),
            " "
          ] }),
          /* @__PURE__ */ jsx(
            Link,
            {
              href: route("register"),
              className: "text-primary hover:underline",
              children: t("register", "Register")
            }
          )
        ] })
      ] })
    ] })
  ] });
}
const __vite_glob_0_2 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Login
}, Symbol.toStringTag, { value: "Module" }));
function Register() {
  const { t } = useI18n();
  const { data, setData, post, processing, errors, reset } = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: ""
  });
  const submit = (e) => {
    e.preventDefault();
    post(route("register"), {
      onFinish: () => reset("password", "password_confirmation")
    });
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("register", "Register") }),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-md space-y-6 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold tracking-tight", children: t("create_account", "Create an account") }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("register_description", "Enter your information to create an account") })
      ] }),
      /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "name", children: t("name", "Name") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "name",
              name: "name",
              value: data.name,
              autoComplete: "name",
              autoFocus: true,
              onChange: (e) => setData("name", e.target.value),
              className: errors.name ? "border-destructive" : ""
            }
          ),
          errors.name && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.name })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "email", children: t("email", "Email") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "email",
              type: "email",
              name: "email",
              value: data.email,
              autoComplete: "username",
              onChange: (e) => setData("email", e.target.value),
              className: errors.email ? "border-destructive" : ""
            }
          ),
          errors.email && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.email })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "password", children: t("password", "Password") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "password",
              type: "password",
              name: "password",
              value: data.password,
              autoComplete: "new-password",
              onChange: (e) => setData("password", e.target.value),
              className: errors.password ? "border-destructive" : ""
            }
          ),
          errors.password && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.password })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "password_confirmation", children: t("confirm_password", "Confirm Password") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "password_confirmation",
              type: "password",
              name: "password_confirmation",
              value: data.password_confirmation,
              autoComplete: "new-password",
              onChange: (e) => setData("password_confirmation", e.target.value),
              className: errors.password_confirmation ? "border-destructive" : ""
            }
          ),
          errors.password_confirmation && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.password_confirmation })
        ] }),
        /* @__PURE__ */ jsx(
          Button,
          {
            type: "submit",
            className: "w-full",
            disabled: processing,
            children: t("register", "Register")
          }
        ),
        /* @__PURE__ */ jsxs("div", { className: "relative my-4", children: [
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center", children: /* @__PURE__ */ jsx("div", { className: "w-full border-t border-muted" }) }),
          /* @__PURE__ */ jsx("div", { className: "relative flex justify-center text-xs uppercase", children: /* @__PURE__ */ jsx("span", { className: "bg-background px-2 text-muted-foreground", children: t("or_continue_with", "Or continue with") }) })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-4", children: [
          /* @__PURE__ */ jsxs(
            Button,
            {
              variant: "outline",
              onClick: () => window.location.href = route("social.login", { provider: "facebook" }),
              className: "flex items-center justify-center",
              children: [
                /* @__PURE__ */ jsx(Facebook, { className: "mr-2 h-4 w-4" }),
                "Facebook"
              ]
            }
          ),
          /* @__PURE__ */ jsxs(
            Button,
            {
              variant: "outline",
              onClick: () => window.location.href = route("social.login", { provider: "google" }),
              className: "flex items-center justify-center",
              children: [
                /* @__PURE__ */ jsx(Chrome, { className: "mr-2 h-4 w-4" }),
                "Google"
              ]
            }
          )
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "text-center text-sm", children: [
          /* @__PURE__ */ jsxs("span", { className: "text-muted-foreground", children: [
            t("already_registered", "Already registered?"),
            " "
          ] }),
          /* @__PURE__ */ jsx(
            Link,
            {
              href: route("login"),
              className: "text-primary hover:underline",
              children: t("login", "Log in")
            }
          )
        ] })
      ] })
    ] })
  ] });
}
const __vite_glob_0_3 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Register
}, Symbol.toStringTag, { value: "Module" }));
function ResetPassword({ token, email: email2 }) {
  const { t } = useI18n();
  const { data, setData, post, processing, errors, reset } = useForm({
    token,
    email: email2,
    password: "",
    password_confirmation: ""
  });
  useEffect(() => {
    return () => {
      reset("password", "password_confirmation");
    };
  }, []);
  const submit = (e) => {
    e.preventDefault();
    post(route("password.store"));
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("reset_password", "Reset Password") }),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-md space-y-6 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold tracking-tight", children: t("reset_password", "Reset Password") }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("reset_password_description", "Create a new secure password for your account") })
      ] }),
      /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "email", children: t("email", "Email") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "email",
              type: "email",
              name: "email",
              value: data.email,
              autoComplete: "username",
              onChange: (e) => setData("email", e.target.value),
              className: errors.email ? "border-destructive" : "",
              readOnly: true
            }
          ),
          errors.email && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.email })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "password", children: t("password", "Password") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "password",
              type: "password",
              name: "password",
              value: data.password,
              autoComplete: "new-password",
              autoFocus: true,
              onChange: (e) => setData("password", e.target.value),
              className: errors.password ? "border-destructive" : ""
            }
          ),
          errors.password && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.password })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "password_confirmation", children: t("confirm_password", "Confirm Password") }),
          /* @__PURE__ */ jsx(
            Input,
            {
              id: "password_confirmation",
              type: "password",
              name: "password_confirmation",
              value: data.password_confirmation,
              autoComplete: "new-password",
              onChange: (e) => setData("password_confirmation", e.target.value),
              className: errors.password_confirmation ? "border-destructive" : ""
            }
          ),
          errors.password_confirmation && /* @__PURE__ */ jsx("p", { className: "text-xs text-destructive", children: errors.password_confirmation })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "flex items-center justify-end", children: /* @__PURE__ */ jsx(Button, { type: "submit", disabled: processing, children: t("reset_password", "Reset Password") }) })
      ] })
    ] })
  ] });
}
const __vite_glob_0_4 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ResetPassword
}, Symbol.toStringTag, { value: "Module" }));
function VerifyEmail({ status }) {
  var _a;
  const { t } = useI18n();
  const submit = (e) => {
    e.preventDefault();
    router.post(route("verification.send"));
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("verify_email", "Email Verification") }),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-md space-y-6 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold tracking-tight", children: t("verify_email", "Email Verification") }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("verify_email_description", "Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?") })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "mt-4 text-sm", children: /* @__PURE__ */ jsx("p", { className: "text-muted-foreground", children: t("verify_email_not_received", "If you didn't receive the email, we will gladly send you another.") }) }),
      status === "verification-link-sent" && /* @__PURE__ */ jsx(Alert, { variant: "success", className: "my-4", children: /* @__PURE__ */ jsx(AlertDescription, { children: t("verification_link_sent", "A new verification link has been sent to the email address you provided during registration.") }) }),
      /* @__PURE__ */ jsxs("div", { className: "mt-6 flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0", children: [
        /* @__PURE__ */ jsx("form", { onSubmit: submit, children: /* @__PURE__ */ jsx(Button, { type: "submit", children: t("resend_verification_email", "Resend Verification Email") }) }),
        /* @__PURE__ */ jsx(
          Link,
          {
            href: route("profile.edit"),
            className: "text-sm text-primary hover:underline",
            children: t("update_profile", "Edit Profile")
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("form", { method: "POST", action: route("logout"), className: "mt-4 text-center", children: [
        /* @__PURE__ */ jsx("input", { type: "hidden", name: "_token", value: ((_a = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : _a.getAttribute("content")) || "" }),
        /* @__PURE__ */ jsx(
          Button,
          {
            type: "submit",
            variant: "ghost",
            className: "text-sm text-primary hover:underline",
            children: t("logout", "Log Out")
          }
        )
      ] })
    ] })
  ] });
}
const __vite_glob_0_5 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: VerifyEmail
}, Symbol.toStringTag, { value: "Module" }));
function resolveStoragePath(path) {
  if (!path || path.trim() === "") {
    return null;
  }
  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }
  if (path.startsWith("/storage/")) {
    return path;
  }
  return `/storage/${path}`;
}
const Card = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    className: cn(
      "rounded-xl border bg-card text-card-foreground shadow",
      className
    ),
    ...props
  }
));
Card.displayName = "Card";
const CardHeader = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    className: cn("flex flex-col space-y-1.5 p-6", className),
    ...props
  }
));
CardHeader.displayName = "CardHeader";
const CardTitle = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    className: cn("font-semibold leading-none tracking-tight", className),
    ...props
  }
));
CardTitle.displayName = "CardTitle";
const CardDescription = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    className: cn("text-sm text-muted-foreground", className),
    ...props
  }
));
CardDescription.displayName = "CardDescription";
const CardContent = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx("div", { ref, className: cn("p-6 pt-0", className), ...props }));
CardContent.displayName = "CardContent";
const CardFooter = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  "div",
  {
    ref,
    className: cn("flex items-center p-6 pt-0", className),
    ...props
  }
));
CardFooter.displayName = "CardFooter";
const Image = React__default.forwardRef(
  ({
    className,
    src,
    alt = "",
    fallback,
    fallbackSrc,
    onError,
    useDefaultFallback = true,
    ...props
  }, ref) => {
    const [error, setError] = useState(false);
    const defaultFallbackSrc = "/placeholder.jpg";
    const resolvedSrc = resolveStoragePath(src);
    const handleError = (e) => {
      setError(true);
      if (onError) {
        onError(e);
      }
    };
    if (error) {
      if (fallbackSrc) {
        return /* @__PURE__ */ jsx(
          "img",
          {
            ref,
            className: cn(className),
            src: resolveStoragePath(fallbackSrc) || fallbackSrc,
            alt,
            ...props
          }
        );
      }
      if (useDefaultFallback) {
        return /* @__PURE__ */ jsx(
          "img",
          {
            ref,
            className: cn(className),
            src: defaultFallbackSrc,
            alt,
            ...props
          }
        );
      }
      if (fallback) {
        return /* @__PURE__ */ jsx(Fragment, { children: fallback });
      }
      return /* @__PURE__ */ jsx(
        "div",
        {
          className: cn("bg-muted", className),
          role: "img",
          "aria-label": alt,
          ...props
        }
      );
    }
    return /* @__PURE__ */ jsx(
      "img",
      {
        ref,
        className: cn(className),
        src: resolvedSrc || void 0,
        alt,
        onError: handleError,
        ...props
      }
    );
  }
);
Image.displayName = "Image";
const EmptyState = ({
  icon,
  title,
  description: description2,
  action
}) => {
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center justify-center py-12 px-4 text-center bg-muted/30 rounded-lg", children: [
    icon && /* @__PURE__ */ jsx("div", { className: "text-muted-foreground mb-4", children: icon }),
    /* @__PURE__ */ jsx("h3", { className: "text-xl font-medium mb-2", children: title }),
    description2 && /* @__PURE__ */ jsx("p", { className: "text-muted-foreground mb-6 max-w-md", children: description2 }),
    action && /* @__PURE__ */ jsx("div", { children: action })
  ] });
};
function Index$5({ brands: brands2 }) {
  const { t, getLocalizedField } = useI18n();
  return /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
    /* @__PURE__ */ jsx(Head, { title: t("brands", "Brands") }),
    /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mb-8", children: [
      /* @__PURE__ */ jsx(Award, { className: "h-7 w-7 text-primary" }),
      /* @__PURE__ */ jsx("h1", { className: "text-2xl md:text-3xl font-bold tracking-tight", children: t("brands", "Brands") })
    ] }),
    brands2 && brands2.length > 0 ? /* @__PURE__ */ jsx("div", { className: "grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6", children: brands2.map((brand, index) => {
      const bgColors = [
        "from-teal-400 to-cyan-500",
        "from-blue-500 to-blue-600",
        "from-sky-400 to-blue-500",
        "from-indigo-500 to-purple-600",
        "from-cyan-400 to-teal-500",
        "from-blue-400 to-indigo-500"
      ];
      const bgColor = bgColors[index % bgColors.length];
      return /* @__PURE__ */ jsx(
        Card,
        {
          className: "group relative aspect-[4/3] transition-all duration-300 hover:shadow-xl overflow-hidden border-none rounded-xl",
          children: /* @__PURE__ */ jsx(
            Link,
            {
              href: `/search?brands[]=${brand.id}`,
              className: "block h-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-xl",
              children: /* @__PURE__ */ jsxs("div", { className: `relative h-full bg-gradient-to-br ${bgColor}`, children: [
                brand.display_image || brand.image ? /* @__PURE__ */ jsxs("div", { className: "relative w-full h-full", children: [
                  /* @__PURE__ */ jsx(
                    Image,
                    {
                      src: resolveStoragePath(brand.display_image || brand.image) || "",
                      alt: getLocalizedField(
                        brand,
                        "name"
                      ),
                      className: "object-cover w-full h-full transition-transform duration-500 group-hover:scale-105 rounded-xl",
                      sizes: "(max-width: 640px) 50vw, (max-width: 768px) 50vw, 25vw",
                      fallback: /* @__PURE__ */ jsx("div", { className: `w-full h-full flex items-center justify-center bg-gradient-to-br ${bgColor} rounded-xl`, children: /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-white", children: getLocalizedField(
                        brand,
                        "name"
                      ) }) })
                    }
                  ),
                  /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-xl" })
                ] }) : /* @__PURE__ */ jsx("div", { className: `w-full h-full flex items-center justify-center bg-gradient-to-br ${bgColor} text-white rounded-xl`, children: /* @__PURE__ */ jsx("span", { className: "text-sm font-medium", children: getLocalizedField(
                  brand,
                  "name"
                ) }) }),
                /* @__PURE__ */ jsx("div", { className: "absolute bottom-0 left-0 right-0 p-4 text-white", children: /* @__PURE__ */ jsx("div", { className: "space-y-1", children: /* @__PURE__ */ jsx("h3", { className: "font-bold text-sm leading-tight uppercase tracking-wide", children: getLocalizedField(
                  brand,
                  "name"
                ) }) }) })
              ] })
            }
          )
        },
        brand.id
      );
    }) }) : /* @__PURE__ */ jsx(
      EmptyState,
      {
        icon: /* @__PURE__ */ jsx(ShoppingBag, { className: "h-8 w-8 text-muted-foreground" }),
        title: t("no_brands_available", "No brands available"),
        description: t(
          "no_brands_available_description",
          "There are no brands available at the moment."
        )
      }
    )
  ] });
}
const __vite_glob_0_6 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$5
}, Symbol.toStringTag, { value: "Module" }));
const badgeVariants = cva(
  "inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
  {
    variants: {
      variant: {
        default: "border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80",
        secondary: "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
        destructive: "border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80",
        outline: "text-foreground"
      }
    },
    defaultVariants: {
      variant: "default"
    }
  }
);
function Badge({ className, variant: variant2, ...props }) {
  return /* @__PURE__ */ jsx("div", { className: cn(badgeVariants({ variant: variant2 }), className), ...props });
}
function CartItem({
  item,
  isLoading,
  updateCartItemQuantity,
  removeCartItem,
  calculateItemTotal
}) {
  var _a, _b, _c, _d, _e, _f, _g, _h, _i;
  const { t, getLocalizedField } = useI18n();
  return /* @__PURE__ */ jsx(Card, { className: "overflow-hidden", children: /* @__PURE__ */ jsx("div", { className: "p-3 sm:p-4", children: /* @__PURE__ */ jsxs("div", { className: "flex flex-row gap-3 sm:gap-4 items-start", children: [
    /* @__PURE__ */ jsx("div", { className: "h-14 w-14 sm:h-24 sm:w-24 rounded-md overflow-hidden bg-muted flex-shrink-0", children: /* @__PURE__ */ jsx(
      Image,
      {
        src: ((_a = item.variant) == null ? void 0 : _a.featured_image) || item.product.featured_image || "/placeholder.jpg",
        alt: item.product.name_en,
        className: "h-full w-full object-cover",
        fallbackSrc: "/placeholder.jpg"
      }
    ) }),
    /* @__PURE__ */ jsxs("div", { className: "flex-grow space-y-3 sm:space-y-4 w-full", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start gap-2", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("h4", { className: "font-medium line-clamp-2 text-sm sm:text-base", children: getLocalizedField(item.product, "name") }),
          item.variant && /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-1.5 mt-1.5", children: [
            item.variant.color && /* @__PURE__ */ jsxs(Badge, { variant: "outline", className: "px-2 py-0 h-5 text-xs", children: [
              t("color", "Color"),
              ": ",
              item.variant.color
            ] }),
            item.variant.size && /* @__PURE__ */ jsxs(Badge, { variant: "outline", className: "px-2 py-0 h-5 text-xs", children: [
              t("size", "Size"),
              ": ",
              item.variant.size
            ] }),
            item.variant.capacity && /* @__PURE__ */ jsxs(Badge, { variant: "outline", className: "px-2 py-0 h-5 text-xs", children: [
              t("capacity", "Capacity"),
              ": ",
              item.variant.capacity
            ] })
          ] })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "flex items-center ml-1", children: /* @__PURE__ */ jsx(
          Button,
          {
            variant: "ghost",
            size: "icon",
            disabled: isLoading[item.id],
            onClick: () => removeCartItem(item.id),
            className: "text-destructive hover:text-destructive/80 h-7 w-7 sm:h-8 sm:w-8",
            children: /* @__PURE__ */ jsx(Trash2, { className: "h-3.5 w-3.5 sm:h-4 sm:w-4" })
          }
        ) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-3 sm:hidden", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("div", { className: "text-xs font-medium text-muted-foreground mb-0.5", children: t("price", "Price") }),
            /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
              (((_b = item.variant) == null ? void 0 : _b.sale_price) || item.product.sale_price) && /* @__PURE__ */ jsxs("span", { className: "text-primary font-medium text-sm", children: [
                "EGP",
                Number(
                  ((_c = item.variant) == null ? void 0 : _c.sale_price) || item.product.sale_price
                ).toFixed(2)
              ] }),
              /* @__PURE__ */ jsxs(
                "span",
                {
                  className: ((_d = item.variant) == null ? void 0 : _d.sale_price) || item.product.sale_price ? "text-xs line-through text-muted-foreground" : "text-primary font-medium text-sm",
                  children: [
                    "EGP",
                    Number(((_e = item.variant) == null ? void 0 : _e.price) || item.product.price).toFixed(
                      2
                    )
                  ]
                }
              )
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("div", { className: "text-xs font-medium text-muted-foreground mb-0.5", children: t("quantity", "Quantity") }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
              /* @__PURE__ */ jsx(
                Button,
                {
                  size: "icon",
                  variant: "outline",
                  className: "h-7 w-7",
                  disabled: isLoading[item.id] || item.quantity <= 1,
                  onClick: () => updateCartItemQuantity(
                    item.id,
                    item.quantity - 1
                  ),
                  children: /* @__PURE__ */ jsx(MinusCircle, { className: "h-3.5 w-3.5" })
                }
              ),
              /* @__PURE__ */ jsx("span", { className: "w-7 text-center text-sm", children: item.quantity }),
              /* @__PURE__ */ jsx(
                Button,
                {
                  size: "icon",
                  variant: "outline",
                  className: "h-7 w-7",
                  disabled: isLoading[item.id],
                  onClick: () => updateCartItemQuantity(
                    item.id,
                    item.quantity + 1
                  ),
                  children: /* @__PURE__ */ jsx(PlusCircle, { className: "h-3.5 w-3.5" })
                }
              )
            ] })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
          /* @__PURE__ */ jsx("div", { className: "text-xs font-medium text-muted-foreground mb-0.5", children: t("total", "Total") }),
          /* @__PURE__ */ jsxs("div", { className: "font-semibold text-sm", children: [
            "EGP ",
            calculateItemTotal(item)
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "hidden sm:flex sm:flex-row sm:items-center justify-between gap-4", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("div", { className: "text-sm font-medium text-muted-foreground mb-1", children: t("price", "Price") }),
          /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
            (((_f = item.variant) == null ? void 0 : _f.sale_price) || item.product.sale_price) && /* @__PURE__ */ jsxs("span", { className: "text-primary font-medium", children: [
              "EGP",
              Number(
                ((_g = item.variant) == null ? void 0 : _g.sale_price) || item.product.sale_price
              ).toFixed(2)
            ] }),
            /* @__PURE__ */ jsxs(
              "span",
              {
                className: ((_h = item.variant) == null ? void 0 : _h.sale_price) || item.product.sale_price ? "text-xs line-through text-muted-foreground" : "text-primary font-medium",
                children: [
                  "EGP ",
                  Number(((_i = item.variant) == null ? void 0 : _i.price) || item.product.price).toFixed(2)
                ]
              }
            )
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("div", { className: "text-sm font-medium text-muted-foreground mb-1", children: t("quantity", "Quantity") }),
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
            /* @__PURE__ */ jsx(
              Button,
              {
                size: "icon",
                variant: "outline",
                className: "h-8 w-8",
                disabled: isLoading[item.id] || item.quantity <= 1,
                onClick: () => updateCartItemQuantity(
                  item.id,
                  item.quantity - 1
                ),
                children: /* @__PURE__ */ jsx(MinusCircle, { className: "h-4 w-4" })
              }
            ),
            /* @__PURE__ */ jsx("span", { className: "w-8 text-center", children: item.quantity }),
            /* @__PURE__ */ jsx(
              Button,
              {
                size: "icon",
                variant: "outline",
                className: "h-8 w-8",
                disabled: isLoading[item.id],
                onClick: () => updateCartItemQuantity(
                  item.id,
                  item.quantity + 1
                ),
                children: /* @__PURE__ */ jsx(PlusCircle, { className: "h-4 w-4" })
              }
            )
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("div", { className: "text-sm font-medium text-muted-foreground mb-1", children: t("total", "Total") }),
          /* @__PURE__ */ jsxs("div", { className: "font-semibold", children: [
            "EGP ",
            calculateItemTotal(item)
          ] })
        ] })
      ] })
    ] })
  ] }) }) });
}
function CartItemList({
  items: items2,
  isLoading,
  updateCartItemQuantity,
  removeCartItem,
  calculateItemTotal,
  clearCart
}) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs("div", { className: "lg:col-span-2", children: [
    /* @__PURE__ */ jsx("div", { className: "space-y-4", children: items2.map((item) => /* @__PURE__ */ jsx(
      CartItem,
      {
        item,
        isLoading,
        updateCartItemQuantity,
        removeCartItem,
        calculateItemTotal
      },
      item.id
    )) }),
    /* @__PURE__ */ jsxs("div", { className: "mt-4 flex justify-between", children: [
      /* @__PURE__ */ jsx(
        Button,
        {
          variant: "outline",
          onClick: () => router.visit(route("home")),
          children: t("continue_shopping", "Continue Shopping")
        }
      ),
      /* @__PURE__ */ jsx(
        Button,
        {
          variant: "ghost",
          className: "text-destructive",
          onClick: clearCart,
          children: t("clear_cart", "Clear Cart")
        }
      )
    ] })
  ] });
}
function OrderSummary$2({ cartSummary }) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsx("div", { className: "lg:col-span-1", children: /* @__PURE__ */ jsxs(Card, { className: "p-6", children: [
    /* @__PURE__ */ jsx("h3", { className: "text-lg font-semibold mb-4", children: t("order_summary", "Order Summary") }),
    /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between", children: [
        /* @__PURE__ */ jsx("span", { className: "text-muted-foreground", children: t("subtotal", "Subtotal") }),
        /* @__PURE__ */ jsxs("span", { children: [
          "EGP ",
          cartSummary.totalPrice.toFixed(2)
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between", children: [
        /* @__PURE__ */ jsx("span", { className: "text-muted-foreground", children: t("shipping", "Shipping") }),
        /* @__PURE__ */ jsx("span", { children: t("calculated_at_checkout", "Calculated at checkout") })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "border-t pt-4 flex justify-between font-semibold", children: [
        /* @__PURE__ */ jsx("span", { children: t("total", "Total") }),
        /* @__PURE__ */ jsxs("span", { children: [
          "EGP ",
          cartSummary.totalPrice.toFixed(2)
        ] })
      ] }),
      /* @__PURE__ */ jsx(Button, { onClick: () => router.get(route("checkout.index")), className: "w-full", size: "lg", children: t("proceed_to_checkout", "Proceed to Checkout") })
    ] })
  ] }) });
}
const TOAST_LIMIT = 1;
const TOAST_REMOVE_DELAY = 1e6;
let count = 0;
function genId() {
  count = (count + 1) % Number.MAX_SAFE_INTEGER;
  return count.toString();
}
const toastTimeouts = /* @__PURE__ */ new Map();
const addToRemoveQueue = (toastId) => {
  if (toastTimeouts.has(toastId)) {
    return;
  }
  const timeout = setTimeout(() => {
    toastTimeouts.delete(toastId);
    dispatch({
      type: "REMOVE_TOAST",
      toastId
    });
  }, TOAST_REMOVE_DELAY);
  toastTimeouts.set(toastId, timeout);
};
const reducer = (state, action) => {
  switch (action.type) {
    case "ADD_TOAST":
      return {
        ...state,
        toasts: [action.toast, ...state.toasts].slice(0, TOAST_LIMIT)
      };
    case "UPDATE_TOAST":
      return {
        ...state,
        toasts: state.toasts.map(
          (t) => t.id === action.toast.id ? { ...t, ...action.toast } : t
        )
      };
    case "DISMISS_TOAST": {
      const { toastId } = action;
      if (toastId) {
        addToRemoveQueue(toastId);
      } else {
        state.toasts.forEach((toast2) => {
          addToRemoveQueue(toast2.id);
        });
      }
      return {
        ...state,
        toasts: state.toasts.map(
          (t) => t.id === toastId || toastId === void 0 ? {
            ...t,
            open: false
          } : t
        )
      };
    }
    case "REMOVE_TOAST":
      if (action.toastId === void 0) {
        return {
          ...state,
          toasts: []
        };
      }
      return {
        ...state,
        toasts: state.toasts.filter((t) => t.id !== action.toastId)
      };
  }
};
const listeners = [];
let memoryState = { toasts: [] };
function dispatch(action) {
  memoryState = reducer(memoryState, action);
  listeners.forEach((listener) => {
    listener(memoryState);
  });
}
function toast({ ...props }) {
  const id = genId();
  const update = (props2) => dispatch({
    type: "UPDATE_TOAST",
    toast: { ...props2, id }
  });
  const dismiss = () => dispatch({ type: "DISMISS_TOAST", toastId: id });
  dispatch({
    type: "ADD_TOAST",
    toast: {
      ...props,
      id,
      open: true,
      onOpenChange: (open) => {
        if (!open) dismiss();
      }
    }
  });
  return {
    id,
    dismiss,
    update
  };
}
function useToast() {
  const [state, setState] = React.useState(memoryState);
  React.useEffect(() => {
    listeners.push(setState);
    return () => {
      const index = listeners.indexOf(setState);
      if (index > -1) {
        listeners.splice(index, 1);
      }
    };
  }, [state]);
  return {
    ...state,
    toast,
    dismiss: (toastId) => dispatch({ type: "DISMISS_TOAST", toastId })
  };
}
function useCart() {
  const [isLoading, setIsLoading] = useState({});
  const [addingToCart, setAddingToCart] = useState({});
  const { t } = useI18n();
  const addToCart = async (productId, quantity2 = 1, variantId) => {
    var _a, _b, _c;
    setAddingToCart((prev) => ({ ...prev, [productId]: true }));
    try {
      await axios.post(route("cart.add"), {
        product_id: productId,
        product_variant_id: variantId,
        quantity: quantity2
      });
      toast({
        title: t("added_to_cart", "Added to Cart")
      });
      router.reload({ only: ["cart_summary"] });
    } catch (error) {
      console.error("Error adding product to cart:", error);
      if (((_a = error.response) == null ? void 0 : _a.status) === 401) {
        toast({
          title: t("login_required", "Login Required"),
          description: t(
            "login_to_add_to_cart",
            "Please login to add items to cart"
          ),
          variant: "destructive"
        });
        router.visit(route("login"));
        return;
      }
      const errorMessage = ((_c = (_b = error.response) == null ? void 0 : _b.data) == null ? void 0 : _c.message) || error.message || t(
        "failed_to_add_to_cart",
        "Failed to add item to cart. Please try again."
      );
      toast({
        title: t("error", "Error"),
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setAddingToCart((prev) => ({ ...prev, [productId]: false }));
    }
  };
  const updateCartItemQuantity = async (id, quantity2) => {
    var _a, _b, _c;
    setIsLoading((prev) => ({ ...prev, [id]: true }));
    try {
      await axios.patch(route("cart.update", id), { quantity: quantity2 });
      router.reload();
    } catch (error) {
      console.error("Error updating cart item:", error);
      if (((_a = error.response) == null ? void 0 : _a.status) === 401) {
        toast({
          title: t("login_required", "Login Required"),
          description: t(
            "login_to_continue",
            "Please login to continue"
          ),
          variant: "destructive"
        });
        router.visit(route("login"));
        return;
      }
      const errorMessage = ((_c = (_b = error.response) == null ? void 0 : _b.data) == null ? void 0 : _c.message) || error.message || t(
        "failed_to_update_cart",
        "Failed to update cart item. Please try again."
      );
      toast({
        title: t("error", "Error"),
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsLoading((prev) => ({ ...prev, [id]: false }));
    }
  };
  const removeCartItem = async (id) => {
    var _a, _b, _c;
    setIsLoading((prev) => ({ ...prev, [id]: true }));
    try {
      await axios.delete(route("cart.remove", id));
      router.reload();
    } catch (error) {
      console.error("Error removing cart item:", error);
      if (((_a = error.response) == null ? void 0 : _a.status) === 401) {
        toast({
          title: t("login_required", "Login Required"),
          description: t(
            "login_to_continue",
            "Please login to continue"
          ),
          variant: "destructive"
        });
        router.visit(route("login"));
        return;
      }
      const errorMessage = ((_c = (_b = error.response) == null ? void 0 : _b.data) == null ? void 0 : _c.message) || error.message || t(
        "failed_to_remove_from_cart",
        "Failed to remove item from cart. Please try again."
      );
      toast({
        title: t("error", "Error"),
        description: errorMessage,
        variant: "destructive"
      });
    } finally {
      setIsLoading((prev) => ({ ...prev, [id]: false }));
    }
  };
  const clearCart = async () => {
    var _a, _b, _c;
    try {
      await axios.delete(route("cart.clear"));
      router.reload();
    } catch (error) {
      console.error("Error clearing cart:", error);
      if (((_a = error.response) == null ? void 0 : _a.status) === 401) {
        toast({
          title: t("login_required", "Login Required"),
          description: t(
            "login_to_continue",
            "Please login to continue"
          ),
          variant: "destructive"
        });
        router.visit(route("login"));
        return;
      }
      const errorMessage = ((_c = (_b = error.response) == null ? void 0 : _b.data) == null ? void 0 : _c.message) || error.message || t(
        "failed_to_clear_cart",
        "Failed to clear cart. Please try again."
      );
      toast({
        title: t("error", "Error"),
        description: errorMessage,
        variant: "destructive"
      });
    }
  };
  const calculateItemTotal = (item) => {
    var _a, _b;
    const priceVariant = ((_a = item.variant) == null ? void 0 : _a.sale_price) || ((_b = item.variant) == null ? void 0 : _b.price);
    const priceProduct = item.product.sale_price || item.product.price;
    const price2 = priceVariant || priceProduct;
    return (price2 * item.quantity).toFixed(2);
  };
  return {
    isLoading,
    addingToCart,
    addToCart,
    updateCartItemQuantity,
    removeCartItem,
    clearCart,
    calculateItemTotal
  };
}
function PageTitle({
  title,
  icon,
  backUrl,
  backText,
  actions,
  className = ""
}) {
  const { t, direction } = useI18n();
  const ArrowIcon = direction === "rtl" ? ArrowRight : ArrowLeft;
  return /* @__PURE__ */ jsxs("div", { className: `flex flex-col gap-2 mb-8 ${className}`, children: [
    backUrl && /* @__PURE__ */ jsx(
      Button,
      {
        variant: "ghost",
        size: "sm",
        asChild: true,
        className: "self-start hover:bg-transparent px-0 mb-1",
        children: /* @__PURE__ */ jsxs(Link, { href: backUrl, children: [
          /* @__PURE__ */ jsx(ArrowIcon, { className: "w-4 h-4 mr-2" }),
          backText || t("back", "Back")
        ] })
      }
    ),
    /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
      /* @__PURE__ */ jsxs("h1", { className: "text-3xl font-bold tracking-tight flex items-center gap-2", children: [
        icon,
        title
      ] }),
      actions
    ] })
  ] });
}
function Index$4({ auth, cart: cart2, cartSummary }) {
  const { t } = useI18n();
  const {
    isLoading,
    updateCartItemQuantity,
    removeCartItem,
    clearCart,
    calculateItemTotal
  } = useCart();
  return /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
    /* @__PURE__ */ jsx(Head, { title: t("cart", "Shopping Cart") }),
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-8", children: [
      /* @__PURE__ */ jsx(
        PageTitle,
        {
          title: t("cart", "Shopping Cart"),
          icon: /* @__PURE__ */ jsx(ShoppingBag, { className: "h-6 w-6 text-primary" })
        }
      ),
      (!cart2.items || cart2.items.length === 0) && /* @__PURE__ */ jsx(
        EmptyState,
        {
          icon: /* @__PURE__ */ jsx(
            ShoppingBag,
            {
              size: 36,
              className: "text-muted-foreground"
            }
          ),
          title: t("your_cart_is_empty", "Your cart is empty"),
          description: t(
            "add_items_to_cart",
            "Add items to your cart to see them here"
          ),
          action: /* @__PURE__ */ jsx(Button, { onClick: () => router.visit(route("home")), children: t("continue_shopping", "Continue Shopping") })
        }
      ),
      cart2.items && cart2.items.length > 0 && /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 gap-8 lg:grid-cols-3", children: [
        /* @__PURE__ */ jsx(
          CartItemList,
          {
            items: cart2.items,
            isLoading,
            updateCartItemQuantity,
            removeCartItem,
            calculateItemTotal,
            clearCart
          }
        ),
        /* @__PURE__ */ jsx(OrderSummary$2, { cartSummary })
      ] })
    ] })
  ] });
}
const __vite_glob_0_7 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$4
}, Symbol.toStringTag, { value: "Module" }));
function Index$3({ categories: categories2 }) {
  const { t, getLocalizedField } = useI18n();
  const isFeatureCategory = (index) => {
    return !(index % 3);
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("categories", "Categories") }),
    /* @__PURE__ */ jsx(
      PageTitle,
      {
        title: t("categories", "Categories"),
        icon: /* @__PURE__ */ jsx(Layers, { className: "h-7 w-7 text-primary" })
      }
    ),
    categories2 && categories2.length > 0 ? /* @__PURE__ */ jsx("div", { className: "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 auto-rows-[180px]", children: categories2.map((category2, index) => /* @__PURE__ */ jsx(
      Card,
      {
        className: cn(
          "group relative transition-all duration-300 hover:shadow-lg overflow-hidden border-muted/40 hover:border-primary/30",
          isFeatureCategory(index) && "sm:col-span-2 sm:row-span-2"
        ),
        children: /* @__PURE__ */ jsx(
          Link,
          {
            href: `/search?categories[]=${category2.id}`,
            className: "block h-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2",
            children: /* @__PURE__ */ jsxs("div", { className: "relative h-full bg-background/50 group-hover:bg-muted/5", children: [
              category2.display_image || category2.image ? /* @__PURE__ */ jsxs("div", { className: "relative w-full h-full", children: [
                /* @__PURE__ */ jsx(
                  Image,
                  {
                    src: resolveStoragePath(category2.display_image || category2.image) || "",
                    alt: getLocalizedField(
                      category2,
                      "name"
                    ),
                    className: "object-cover w-full h-full transition-transform duration-500 group-hover:scale-105",
                    sizes: isFeatureCategory(index) ? "(max-width: 640px) 100vw, 66vw" : "(max-width: 640px) 50vw, 33vw",
                    fallback: /* @__PURE__ */ jsx("div", { className: "w-full h-full flex items-center justify-center bg-muted/5", children: /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-muted-foreground", children: getLocalizedField(
                      category2,
                      "name"
                    ) }) })
                  }
                ),
                /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-b from-transparent via-background/10 to-gray-900/90" })
              ] }) : /* @__PURE__ */ jsx("div", { className: "w-full h-full flex items-center justify-center bg-muted/5 text-muted-foreground", children: /* @__PURE__ */ jsx("span", { className: "text-sm font-medium", children: getLocalizedField(
                category2,
                "name"
              ) }) }),
              /* @__PURE__ */ jsx("div", { className: "absolute bottom-0 left-0 right-0 p-4", children: /* @__PURE__ */ jsx("div", { className: "flex items-center gap-2", children: /* @__PURE__ */ jsx(
                "h3",
                {
                  className: cn(
                    "font-medium text-foreground line-clamp-2 text-white",
                    isFeatureCategory(index) ? "text-lg" : "text-sm"
                  ),
                  children: getLocalizedField(
                    category2,
                    "name"
                  )
                }
              ) }) })
            ] })
          }
        )
      },
      category2.id
    )) }) : /* @__PURE__ */ jsx(
      EmptyState,
      {
        icon: /* @__PURE__ */ jsx(FolderX, { className: "h-8 w-8 text-muted-foreground" }),
        title: t(
          "no_categories_available",
          "No categories available"
        ),
        description: t(
          "no_categories_available_description",
          "There are no categories available at the moment."
        )
      }
    )
  ] });
}
const __vite_glob_0_8 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$3
}, Symbol.toStringTag, { value: "Module" }));
const Dialog = DialogPrimitive.Root;
const DialogTrigger = DialogPrimitive.Trigger;
const DialogPortal = DialogPrimitive.Portal;
const DialogOverlay = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DialogPrimitive.Overlay,
  {
    ref,
    className: cn(
      "fixed inset-0 z-50 bg-black/80  data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0",
      className
    ),
    ...props
  }
));
DialogOverlay.displayName = DialogPrimitive.Overlay.displayName;
const DialogContent = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(DialogPortal, { children: [
  /* @__PURE__ */ jsx(DialogOverlay, {}),
  /* @__PURE__ */ jsxs(
    DialogPrimitive.Content,
    {
      ref,
      className: cn(
        "fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] sm:rounded-lg",
        className
      ),
      ...props,
      children: [
        children,
        /* @__PURE__ */ jsxs(DialogPrimitive.Close, { className: "absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground", children: [
          /* @__PURE__ */ jsx(X, { className: "h-4 w-4" }),
          /* @__PURE__ */ jsx("span", { className: "sr-only", children: "Close" })
        ] })
      ]
    }
  )
] }));
DialogContent.displayName = DialogPrimitive.Content.displayName;
const DialogHeader = ({
  className,
  ...props
}) => /* @__PURE__ */ jsx(
  "div",
  {
    className: cn(
      "flex flex-col space-y-1.5 text-center sm:text-left",
      className
    ),
    ...props
  }
);
DialogHeader.displayName = "DialogHeader";
const DialogFooter = ({
  className,
  ...props
}) => /* @__PURE__ */ jsx(
  "div",
  {
    className: cn(
      "flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2",
      className
    ),
    ...props
  }
);
DialogFooter.displayName = "DialogFooter";
const DialogTitle = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DialogPrimitive.Title,
  {
    ref,
    className: cn(
      "text-lg font-semibold leading-none tracking-tight",
      className
    ),
    ...props
  }
));
DialogTitle.displayName = DialogPrimitive.Title.displayName;
const DialogDescription = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DialogPrimitive.Description,
  {
    ref,
    className: cn("text-sm text-muted-foreground", className),
    ...props
  }
));
DialogDescription.displayName = DialogPrimitive.Description.displayName;
const Form = FormProvider;
const FormFieldContext = React.createContext(
  {}
);
const FormField = ({
  ...props
}) => {
  return /* @__PURE__ */ jsx(FormFieldContext.Provider, { value: { name: props.name }, children: /* @__PURE__ */ jsx(Controller, { ...props }) });
};
const useFormField = () => {
  const fieldContext = React.useContext(FormFieldContext);
  const itemContext = React.useContext(FormItemContext);
  const { getFieldState, formState } = useFormContext();
  const fieldState = getFieldState(fieldContext.name, formState);
  if (!fieldContext) {
    throw new Error("useFormField should be used within <FormField>");
  }
  const { id } = itemContext;
  return {
    id,
    name: fieldContext.name,
    formItemId: `${id}-form-item`,
    formDescriptionId: `${id}-form-item-description`,
    formMessageId: `${id}-form-item-message`,
    ...fieldState
  };
};
const FormItemContext = React.createContext(
  {}
);
const FormItem = React.forwardRef(({ className, ...props }, ref) => {
  const id = React.useId();
  return /* @__PURE__ */ jsx(FormItemContext.Provider, { value: { id }, children: /* @__PURE__ */ jsx("div", { ref, className: cn("space-y-2", className), ...props }) });
});
FormItem.displayName = "FormItem";
const FormLabel = React.forwardRef(({ className, ...props }, ref) => {
  const { error, formItemId } = useFormField();
  return /* @__PURE__ */ jsx(
    Label,
    {
      ref,
      className: cn(error && "text-destructive", className),
      htmlFor: formItemId,
      ...props
    }
  );
});
FormLabel.displayName = "FormLabel";
const FormControl = React.forwardRef(({ ...props }, ref) => {
  const { error, formItemId, formDescriptionId, formMessageId } = useFormField();
  return /* @__PURE__ */ jsx(
    Slot,
    {
      ref,
      id: formItemId,
      "aria-describedby": !error ? `${formDescriptionId}` : `${formDescriptionId} ${formMessageId}`,
      "aria-invalid": !!error,
      ...props
    }
  );
});
FormControl.displayName = "FormControl";
const FormDescription = React.forwardRef(({ className, ...props }, ref) => {
  const { formDescriptionId } = useFormField();
  return /* @__PURE__ */ jsx(
    "p",
    {
      ref,
      id: formDescriptionId,
      className: cn("text-[0.8rem] text-muted-foreground", className),
      ...props
    }
  );
});
FormDescription.displayName = "FormDescription";
const FormMessage = React.forwardRef(({ className, children, ...props }, ref) => {
  const { error, formMessageId } = useFormField();
  const body = error ? String((error == null ? void 0 : error.message) ?? "") : children;
  if (!body) {
    return null;
  }
  return /* @__PURE__ */ jsx(
    "p",
    {
      ref,
      id: formMessageId,
      className: cn("text-[0.8rem] font-medium text-destructive", className),
      ...props,
      children: body
    }
  );
});
FormMessage.displayName = "FormMessage";
const Select = SelectPrimitive.Root;
const SelectGroup = SelectPrimitive.Group;
const SelectValue = SelectPrimitive.Value;
const SelectTrigger = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  SelectPrimitive.Trigger,
  {
    ref,
    className: cn(
      "flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background data-[placeholder]:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1",
      className
    ),
    ...props,
    children: [
      children,
      /* @__PURE__ */ jsx(SelectPrimitive.Icon, { asChild: true, children: /* @__PURE__ */ jsx(ChevronDown, { className: "h-4 w-4 opacity-50" }) })
    ]
  }
));
SelectTrigger.displayName = SelectPrimitive.Trigger.displayName;
const SelectScrollUpButton = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  SelectPrimitive.ScrollUpButton,
  {
    ref,
    className: cn(
      "flex cursor-default items-center justify-center py-1",
      className
    ),
    ...props,
    children: /* @__PURE__ */ jsx(ChevronUp, { className: "h-4 w-4" })
  }
));
SelectScrollUpButton.displayName = SelectPrimitive.ScrollUpButton.displayName;
const SelectScrollDownButton = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  SelectPrimitive.ScrollDownButton,
  {
    ref,
    className: cn(
      "flex cursor-default items-center justify-center py-1",
      className
    ),
    ...props,
    children: /* @__PURE__ */ jsx(ChevronDown, { className: "h-4 w-4" })
  }
));
SelectScrollDownButton.displayName = SelectPrimitive.ScrollDownButton.displayName;
const SelectContent = React.forwardRef(({ className, children, position = "popper", ...props }, ref) => /* @__PURE__ */ jsx(SelectPrimitive.Portal, { children: /* @__PURE__ */ jsxs(
  SelectPrimitive.Content,
  {
    ref,
    className: cn(
      "relative z-50 max-h-[--radix-select-content-available-height] min-w-[8rem] overflow-y-auto overflow-x-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 origin-[--radix-select-content-transform-origin]",
      position === "popper" && "data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1",
      className
    ),
    position,
    ...props,
    children: [
      /* @__PURE__ */ jsx(SelectScrollUpButton, {}),
      /* @__PURE__ */ jsx(
        SelectPrimitive.Viewport,
        {
          className: cn(
            "p-1",
            position === "popper" && "h-[var(--radix-select-trigger-height)] w-full min-w-[var(--radix-select-trigger-width)]"
          ),
          children
        }
      ),
      /* @__PURE__ */ jsx(SelectScrollDownButton, {})
    ]
  }
) }));
SelectContent.displayName = SelectPrimitive.Content.displayName;
const SelectLabel = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  SelectPrimitive.Label,
  {
    ref,
    className: cn("px-2 py-1.5 text-sm font-semibold", className),
    ...props
  }
));
SelectLabel.displayName = SelectPrimitive.Label.displayName;
const SelectItem = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  SelectPrimitive.Item,
  {
    ref,
    className: cn(
      "relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
      className
    ),
    ...props,
    children: [
      /* @__PURE__ */ jsx("span", { className: "absolute right-2 flex h-3.5 w-3.5 items-center justify-center", children: /* @__PURE__ */ jsx(SelectPrimitive.ItemIndicator, { children: /* @__PURE__ */ jsx(Check, { className: "h-4 w-4" }) }) }),
      /* @__PURE__ */ jsx(SelectPrimitive.ItemText, { children })
    ]
  }
));
SelectItem.displayName = SelectPrimitive.Item.displayName;
const SelectSeparator = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  SelectPrimitive.Separator,
  {
    ref,
    className: cn("-mx-1 my-1 h-px bg-muted", className),
    ...props
  }
));
SelectSeparator.displayName = SelectPrimitive.Separator.displayName;
const Textarea = React.forwardRef(({ className, ...props }, ref) => {
  return /* @__PURE__ */ jsx(
    "textarea",
    {
      className: cn(
        "flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
        className
      ),
      ref,
      ...props
    }
  );
});
Textarea.displayName = "Textarea";
const formSchema = z.object({
  area_id: z.string().min(1, "Area is required"),
  content: z.string().min(3, "Address content must be at least 3 characters"),
  phone: z.string().min(1, "Phone number is required").max(20, "Phone number must be at most 20 characters")
});
function AddressModal({
  trigger,
  onAddressCreated,
  areas: initialAreas
}) {
  const { t, direction } = useI18n();
  const [isOpen, setIsOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [areas, setAreas] = useState(initialAreas || []);
  const [isLoadingAreas, setIsLoadingAreas] = useState(false);
  const form = useForm$1({
    resolver: zodResolver(formSchema),
    defaultValues: {
      area_id: "",
      content: "",
      phone: ""
    }
  });
  const fetchAreas = async () => {
    if (areas.length === 0 && !isLoadingAreas) {
      try {
        setIsLoadingAreas(true);
        const response = await axios.get(route("addresses.areas"));
        setAreas(response.data.areas);
      } catch (error) {
        console.error("Failed to load areas", error);
      } finally {
        setIsLoadingAreas(false);
      }
    }
  };
  const onSubmit = async (values) => {
    var _a, _b;
    try {
      setIsSubmitting(true);
      const response = await axios.post(route("addresses.store"), {
        area_id: parseInt(values.area_id),
        content: values.content,
        phone: values.phone
      });
      onAddressCreated(response.data.address);
      form.reset();
      setIsOpen(false);
    } catch (error) {
      if ((_b = (_a = error.response) == null ? void 0 : _a.data) == null ? void 0 : _b.errors) {
        Object.keys(error.response.data.errors).forEach((key) => {
          form.setError(key, {
            type: "manual",
            message: error.response.data.errors[key][0]
          });
        });
      }
    } finally {
      setIsSubmitting(false);
    }
  };
  return /* @__PURE__ */ jsxs(
    Dialog,
    {
      open: isOpen,
      onOpenChange: (open) => {
        setIsOpen(open);
        if (open) {
          fetchAreas();
        }
      },
      children: [
        /* @__PURE__ */ jsx(DialogTrigger, { asChild: true, children: trigger }),
        /* @__PURE__ */ jsxs(DialogContent, { className: "sm:max-w-[425px]", children: [
          /* @__PURE__ */ jsxs(DialogHeader, { dir: "rtl", children: [
            /* @__PURE__ */ jsx(DialogTitle, { children: t("add_new_address", "Add New Address") }),
            /* @__PURE__ */ jsx(DialogDescription, { children: t(
              "address_description",
              "Enter your address details below to create a new shipping address."
            ) })
          ] }),
          /* @__PURE__ */ jsx(Form, { ...form, children: /* @__PURE__ */ jsxs(
            "form",
            {
              onSubmit: form.handleSubmit(onSubmit),
              className: "space-y-4",
              children: [
                /* @__PURE__ */ jsx(
                  FormField,
                  {
                    control: form.control,
                    name: "area_id",
                    render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
                      /* @__PURE__ */ jsx(FormLabel, { children: t("area", "Area") }),
                      /* @__PURE__ */ jsxs(
                        Select,
                        {
                          onValueChange: field.onChange,
                          defaultValue: field.value,
                          disabled: isSubmitting,
                          dir: direction,
                          children: [
                            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(SelectTrigger, { children: /* @__PURE__ */ jsx(
                              SelectValue,
                              {
                                placeholder: t(
                                  "select_area",
                                  "Select area"
                                )
                              }
                            ) }) }),
                            " ",
                            /* @__PURE__ */ jsx(SelectContent, { children: isLoadingAreas ? /* @__PURE__ */ jsx(
                              SelectItem,
                              {
                                value: "loading",
                                disabled: true,
                                children: t("loading", "Loading...")
                              }
                            ) : areas.length === 0 ? /* @__PURE__ */ jsx(
                              SelectItem,
                              {
                                value: "none",
                                disabled: true,
                                children: t(
                                  "no_areas",
                                  "No areas available"
                                )
                              }
                            ) : areas.map((area2) => {
                              var _a;
                              return /* @__PURE__ */ jsxs(
                                SelectItem,
                                {
                                  value: area2.id.toString(),
                                  children: [
                                    (_a = area2.gov) == null ? void 0 : _a.name_en,
                                    ",",
                                    " ",
                                    area2.name_en
                                  ]
                                },
                                area2.id
                              );
                            }) })
                          ]
                        }
                      ),
                      /* @__PURE__ */ jsx(FormMessage, {})
                    ] })
                  }
                ),
                /* @__PURE__ */ jsx(
                  FormField,
                  {
                    control: form.control,
                    name: "phone",
                    render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
                      /* @__PURE__ */ jsx(FormLabel, { children: t("phone_number", "Phone Number") }),
                      /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
                        Input,
                        {
                          placeholder: t(
                            "phone_placeholder",
                            "Enter phone number..."
                          ),
                          ...field,
                          disabled: isSubmitting
                        }
                      ) }),
                      /* @__PURE__ */ jsx(FormMessage, {})
                    ] })
                  }
                ),
                /* @__PURE__ */ jsx(
                  FormField,
                  {
                    control: form.control,
                    name: "content",
                    render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
                      /* @__PURE__ */ jsx(FormLabel, { children: t(
                        "address_details",
                        "Address Details"
                      ) }),
                      /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
                        Textarea,
                        {
                          placeholder: t(
                            "address_placeholder",
                            "Building, street, landmark..."
                          ),
                          ...field,
                          disabled: isSubmitting
                        }
                      ) }),
                      /* @__PURE__ */ jsx(FormMessage, {})
                    ] })
                  }
                ),
                /* @__PURE__ */ jsx(DialogFooter, { children: /* @__PURE__ */ jsx(Button, { type: "submit", disabled: isSubmitting, children: isSubmitting ? t("saving_address", "Saving...") : t("save_address", "Save Address") }) })
              ]
            }
          ) })
        ] })
      ]
    }
  );
}
function OrderNotesSection({ control }) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs(Card, { children: [
    /* @__PURE__ */ jsx(CardHeader, { children: /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center", children: [
      /* @__PURE__ */ jsx(Receipt, { className: "w-5 h-5 ltr:mr-2 rtl:ml-2" }),
      t("order_notes", "Order Notes")
    ] }) }),
    /* @__PURE__ */ jsx(CardContent, { children: /* @__PURE__ */ jsx(
      FormField,
      {
        control,
        name: "notes",
        render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
          /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
            Textarea,
            {
              placeholder: t(
                "notes_placeholder",
                "Add any special instructions or notes for your order..."
              ),
              ...field,
              rows: 3
            }
          ) }),
          /* @__PURE__ */ jsx(FormMessage, {})
        ] })
      }
    ) })
  ] });
}
const RadioGroup = React.forwardRef(({ className, ...props }, ref) => {
  return /* @__PURE__ */ jsx(
    RadioGroupPrimitive.Root,
    {
      className: cn("grid gap-2", className),
      ...props,
      ref
    }
  );
});
RadioGroup.displayName = RadioGroupPrimitive.Root.displayName;
const RadioGroupItem = React.forwardRef(({ className, ...props }, ref) => {
  return /* @__PURE__ */ jsx(
    RadioGroupPrimitive.Item,
    {
      ref,
      className: cn(
        "aspect-square h-4 w-4 rounded-full border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50",
        className
      ),
      ...props,
      children: /* @__PURE__ */ jsx(RadioGroupPrimitive.Indicator, { className: "flex items-center justify-center", children: /* @__PURE__ */ jsx(Circle, { className: "h-3.5 w-3.5 fill-primary" }) })
    }
  );
});
RadioGroupItem.displayName = RadioGroupPrimitive.Item.displayName;
function PaymentMethodSection({
  paymentMethods,
  control,
  direction
}) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs(Card, { children: [
    /* @__PURE__ */ jsx(CardHeader, { children: /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center", children: [
      /* @__PURE__ */ jsx(CreditCard, { className: "w-5 h-5 ltr:mr-2 rtl:ml-2" }),
      t("payment_method", "Payment Method")
    ] }) }),
    /* @__PURE__ */ jsx(CardContent, { children: /* @__PURE__ */ jsx(
      FormField,
      {
        control,
        name: "payment_method",
        render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
          /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
            RadioGroup,
            {
              dir: direction,
              className: "space-y-4",
              value: field.value,
              onValueChange: field.onChange,
              children: paymentMethods.map((method) => /* @__PURE__ */ jsxs(
                "div",
                {
                  className: `flex items-center gap-3 p-2 rounded-md border cursor-pointer ${field.value === method ? "border-primary bg-primary/5" : "border-border"}`,
                  children: [
                    /* @__PURE__ */ jsx(
                      RadioGroupItem,
                      {
                        value: method,
                        id: `payment-${method}`
                      }
                    ),
                    /* @__PURE__ */ jsxs(
                      Label,
                      {
                        htmlFor: `payment-${method}`,
                        className: "font-medium cursor-pointer flex py-2 items-center gap-2",
                        children: [
                          method === "cash_on_delivery" && /* @__PURE__ */ jsx(Banknote, { className: "w-4 h-4" }),
                          (method === "credit_card" || method === "wallet") && /* @__PURE__ */ jsx(CreditCard, { className: "w-4 h-4" }),
                          t(
                            `payment_method_${method}`,
                            method === "cash_on_delivery" ? "Cash on Delivery" : method === "credit_card" ? "Credit Card" : method === "wallet" ? "Wallet" : method
                          )
                        ]
                      }
                    )
                  ]
                },
                method
              ))
            }
          ) }),
          /* @__PURE__ */ jsx(FormMessage, {})
        ] })
      }
    ) })
  ] });
}
function ShippingAddressSection({
  addresses,
  control,
  direction
}) {
  const { t, getLocalizedField } = useI18n();
  return /* @__PURE__ */ jsxs(Card, { children: [
    /* @__PURE__ */ jsx(CardHeader, { children: /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center", children: [
      /* @__PURE__ */ jsx(MapPin, { className: "w-5 h-5 ltr:mr-2 rtl:ml-2" }),
      t("shipping_address", "Shipping Address")
    ] }) }),
    /* @__PURE__ */ jsx(CardContent, { children: addresses.length === 0 ? /* @__PURE__ */ jsx(Alert, { children: /* @__PURE__ */ jsx(AlertDescription, { children: t(
      "no_addresses",
      "You have no saved addresses. Please add an address to continue."
    ) }) }) : /* @__PURE__ */ jsx(
      FormField,
      {
        control,
        name: "address_id",
        render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
          /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
            RadioGroup,
            {
              dir: direction,
              className: "space-y-4",
              value: field.value,
              onValueChange: field.onChange,
              children: addresses.map((address) => {
                var _a;
                return /* @__PURE__ */ jsxs(
                  "div",
                  {
                    className: `flex items-start gap-3 p-2 rounded-md border cursor-pointer ${field.value === address.id.toString() ? "border-primary bg-primary/5" : "border-border"}`,
                    children: [
                      /* @__PURE__ */ jsx(
                        RadioGroupItem,
                        {
                          value: address.id.toString(),
                          id: `address-${address.id}`,
                          className: "mt-1"
                        }
                      ),
                      /* @__PURE__ */ jsx(
                        Label,
                        {
                          htmlFor: `address-${address.id}`,
                          className: "font-medium cursor-pointer flex-1 py-2",
                          children: /* @__PURE__ */ jsxs("div", { className: "grid gap-1", children: [
                            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
                              getLocalizedField(
                                (_a = address.area) == null ? void 0 : _a.gov,
                                "name"
                              ),
                              ",",
                              " ",
                              getLocalizedField(
                                address.area,
                                "name"
                              )
                            ] }),
                            /* @__PURE__ */ jsx("div", { className: "text-sm text-muted-foreground", children: address.content }),
                            address.phone && /* @__PURE__ */ jsxs("div", { className: "text-sm text-muted-foreground", children: [
                              t("phone", "Phone"),
                              ": ",
                              address.phone
                            ] })
                          ] })
                        }
                      )
                    ]
                  },
                  address.id
                );
              })
            }
          ) }),
          /* @__PURE__ */ jsx(FormMessage, {})
        ] })
      }
    ) })
  ] });
}
function CheckoutForm({
  addresses,
  paymentMethods,
  control,
  direction
}) {
  return /* @__PURE__ */ jsxs("div", { className: "md:col-span-2 space-y-6", children: [
    /* @__PURE__ */ jsx(
      ShippingAddressSection,
      {
        addresses,
        control,
        direction
      }
    ),
    /* @__PURE__ */ jsx(
      PaymentMethodSection,
      {
        paymentMethods,
        control,
        direction
      }
    ),
    /* @__PURE__ */ jsx(OrderNotesSection, { control })
  ] });
}
function getCurrencySymbol(locale = "en") {
  return locale === "ar" ? "ج.م" : "EGP";
}
function formatCurrencyLocalized(amount, locale = "en", includeSymbol = true, decimals = 2) {
  const numericAmount = typeof amount === "string" ? parseFloat(amount) : amount;
  const formattedAmount = numericAmount.toFixed(decimals);
  if (includeSymbol) {
    const symbol = getCurrencySymbol(locale);
    return `${symbol} ${formattedAmount}`;
  }
  return formattedAmount;
}
function CouponSection({
  control,
  appliedPromotion,
  discount: discount2,
  isApplyingCoupon,
  onApplyCoupon,
  onRemoveCoupon,
  couponError,
  onCouponCodeInput
}) {
  const { t, getLocalizedField, currentLocale } = useI18n();
  return /* @__PURE__ */ jsx("div", { className: "pt-4 border-t", children: /* @__PURE__ */ jsx(
    FormField,
    {
      control,
      name: "coupon_code",
      render: ({ field }) => {
        var _a;
        return /* @__PURE__ */ jsxs(FormItem, { children: [
          /* @__PURE__ */ jsx(Label, { children: t("coupon_code", "Coupon Code") }),
          /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
              Input,
              {
                placeholder: t(
                  "enter_coupon_code",
                  "Enter coupon code"
                ),
                ...field,
                disabled: isApplyingCoupon,
                onChange: (e) => {
                  field.onChange(e);
                  if (onCouponCodeInput) {
                    onCouponCodeInput();
                  }
                },
                className: appliedPromotion ? "border-green-200 bg-green-50/50 text-green-700 dark:border-green-800 dark:bg-green-950/10 dark:text-green-300" : couponError ? "border-red-200 bg-red-50/50 text-red-700 dark:border-red-800 dark:bg-red-950/10 dark:text-red-300" : ""
              }
            ) }),
            appliedPromotion ? /* @__PURE__ */ jsx(
              Button,
              {
                type: "button",
                variant: "outline",
                onClick: onRemoveCoupon,
                disabled: isApplyingCoupon,
                className: "whitespace-nowrap border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/20",
                children: isApplyingCoupon ? t("removing", "Removing...") : /* @__PURE__ */ jsxs(Fragment, { children: [
                  /* @__PURE__ */ jsx(X, { className: "h-4 w-4 ltr:mr-1 rtl:ml-1" }),
                  t("remove", "Remove")
                ] })
              }
            ) : /* @__PURE__ */ jsx(
              Button,
              {
                type: "button",
                variant: "outline",
                onClick: onApplyCoupon,
                disabled: isApplyingCoupon || !((_a = field.value) == null ? void 0 : _a.trim()),
                className: "whitespace-nowrap border-green-200 text-green-600 hover:bg-green-50 hover:border-green-300 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-950/20",
                children: isApplyingCoupon ? t("applying", "Applying...") : /* @__PURE__ */ jsxs(Fragment, { children: [
                  /* @__PURE__ */ jsx(Tag, { className: "h-4 w-4 ltr:mr-1 rtl:ml-1" }),
                  t("apply", "Apply")
                ] })
              }
            )
          ] }),
          appliedPromotion && discount2 > 0 && /* @__PURE__ */ jsx("div", { className: "mt-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/20 dark:to-emerald-950/20 border border-green-200 dark:border-green-800 rounded-lg", children: /* @__PURE__ */ jsxs("div", { className: "flex items-start gap-3", children: [
            /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex-shrink-0", children: /* @__PURE__ */ jsx(Gift, { className: "h-4 w-4 text-green-600 dark:text-green-400" }) }),
            /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mb-1", children: [
                /* @__PURE__ */ jsx(CircleCheck, { className: "h-4 w-4 text-green-600 dark:text-green-400" }),
                /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-green-700 dark:text-green-300", children: t(
                  "promotion_applied",
                  "Promotion Applied Successfully!"
                ) })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 flex-wrap", children: [
                /* @__PURE__ */ jsxs("span", { className: "text-xs text-muted-foreground", children: [
                  t("code", "Code"),
                  ":"
                ] }),
                /* @__PURE__ */ jsx(
                  Badge,
                  {
                    variant: "secondary",
                    className: "font-mono bg-green-100 text-green-700 border-green-200 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800",
                    children: appliedPromotion.code
                  }
                ),
                /* @__PURE__ */ jsx("span", { className: "text-xs text-muted-foreground", children: "•" }),
                /* @__PURE__ */ jsxs(
                  Badge,
                  {
                    variant: "default",
                    className: "bg-green-600 text-white hover:bg-green-600 dark:bg-green-500 dark:hover:bg-green-500",
                    children: [
                      /* @__PURE__ */ jsx(Percent, { className: "h-3 w-3 ltr:mr-1 rtl:ml-1" }),
                      t("saving", "Saving"),
                      " ",
                      formatCurrencyLocalized(discount2, currentLocale)
                    ]
                  }
                )
              ] }),
              appliedPromotion.description_en && /* @__PURE__ */ jsx("p", { className: "text-xs text-muted-foreground mt-1 line-clamp-2", children: getLocalizedField(
                appliedPromotion,
                "description"
              ) })
            ] })
          ] }) }),
          couponError && /* @__PURE__ */ jsx("div", { className: "mt-2 p-3 bg-red-50 dark:bg-red-950/20 rounded-lg border border-red-200 dark:border-red-800", children: /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
            /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-full flex-shrink-0", children: /* @__PURE__ */ jsx(AlertTriangle, { className: "h-3 w-3 text-red-600 dark:text-red-400" }) }),
            /* @__PURE__ */ jsx("span", { className: "text-sm text-red-700 dark:text-red-300", children: couponError })
          ] }) }),
          /* @__PURE__ */ jsx(FormMessage, {})
        ] });
      }
    }
  ) });
}
function OrderSummary$1({
  cartSummary,
  subtotal: subtotal2,
  shippingCost,
  discount: discount2,
  total: total2,
  appliedPromotion,
  selectedAddressId,
  control,
  watch,
  isSubmitting,
  isApplyingCoupon,
  onApplyCoupon,
  onRemoveCoupon,
  addressesLength,
  couponError,
  onCouponCodeInput
}) {
  const { t, currentLocale } = useI18n();
  return /* @__PURE__ */ jsxs(Card, { className: "sticky top-4", children: [
    /* @__PURE__ */ jsxs(CardHeader, { children: [
      /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center", children: [
        /* @__PURE__ */ jsx(ShoppingBag, { className: "w-5 h-5 ltr:mr-2 rtl:ml-2" }),
        t("order_summary", "Order Summary")
      ] }),
      /* @__PURE__ */ jsxs(CardDescription, { children: [
        t("items_in_cart", "Items in your cart"),
        ": ",
        cartSummary.totalItems
      ] })
    ] }),
    /* @__PURE__ */ jsxs(CardContent, { className: "space-y-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "space-y-3", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
          /* @__PURE__ */ jsx("span", { className: "text-muted-foreground", children: t("subtotal", "Subtotal") }),
          /* @__PURE__ */ jsx("span", { children: formatCurrencyLocalized(subtotal2, currentLocale) })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-1", children: [
            /* @__PURE__ */ jsx(Truck, { className: "h-4 w-4 text-muted-foreground" }),
            /* @__PURE__ */ jsx("span", { className: "text-muted-foreground", children: t("shipping", "Shipping") })
          ] }),
          /* @__PURE__ */ jsx("span", { children: selectedAddressId ? formatCurrencyLocalized(shippingCost, currentLocale) : t("select_address", "Select address") })
        ] }),
        discount2 > 0 && /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between p-3 bg-green-50 dark:bg-green-950/20 rounded-lg border border-green-200 dark:border-green-800", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
            /* @__PURE__ */ jsx("div", { className: "flex items-center justify-center w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full", children: /* @__PURE__ */ jsx(Tag, { className: "h-3 w-3 text-green-600 dark:text-green-400" }) }),
            /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
              /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-green-700 dark:text-green-300", children: t("discount_applied", "Discount Applied") }),
              appliedPromotion && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-1", children: [
                /* @__PURE__ */ jsx(
                  Badge,
                  {
                    variant: "outline",
                    className: "text-xs font-mono bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800",
                    children: appliedPromotion.code
                  }
                ),
                /* @__PURE__ */ jsx(Percent, { className: "h-3 w-3 text-green-500" })
              ] })
            ] })
          ] }),
          /* @__PURE__ */ jsx("span", { className: "text-lg font-semibold text-green-600 dark:text-green-400", children: /* @__PURE__ */ jsxs("span", { className: "text-destructive", children: [
            "-",
            formatCurrencyLocalized(discount2, currentLocale)
          ] }) })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "border-t pt-3 mt-3", children: /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between font-medium", children: [
          /* @__PURE__ */ jsx("span", { children: t("total", "Total") }),
          /* @__PURE__ */ jsx("span", { className: "text-lg", children: selectedAddressId ? formatCurrencyLocalized(total2, currentLocale) : formatCurrencyLocalized(subtotal2, currentLocale) })
        ] }) })
      ] }),
      /* @__PURE__ */ jsx(
        CouponSection,
        {
          control,
          appliedPromotion,
          discount: discount2,
          isApplyingCoupon,
          onApplyCoupon,
          onRemoveCoupon,
          couponError,
          onCouponCodeInput
        }
      )
    ] }),
    /* @__PURE__ */ jsx(CardFooter, { children: /* @__PURE__ */ jsx(
      Button,
      {
        type: "submit",
        className: "w-full",
        size: "lg",
        disabled: isSubmitting || addressesLength === 0 || !selectedAddressId,
        children: isSubmitting ? t("processing_order", "Processing...") : /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx(CircleCheck, { className: "w-4 h-4 ltr:mr-2 rtl:ml-2" }),
          t("place_order", "Place Order")
        ] })
      }
    ) })
  ] });
}
const checkoutFormSchema = z.object({
  address_id: z.string({
    required_error: "Please select a shipping address"
  }),
  payment_method: z.string({
    required_error: "Please select a payment method"
  }),
  notes: z.string().optional(),
  coupon_code: z.string().optional()
});
function Index$2({
  orderSummary: initialOrderSummary,
  cartSummary,
  addresses,
  paymentMethods
}) {
  var _a;
  const { t, direction } = useI18n();
  const [orderSummary, setOrderSummary] = useState(initialOrderSummary);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isApplyingCoupon, setIsApplyingCoupon] = useState(false);
  const [couponError, setCouponError] = useState(null);
  const { toast: toast2 } = useToast();
  const urlParams = new URLSearchParams(window.location.search);
  const initialCouponCode = urlParams.get("coupon_code") || ((_a = orderSummary == null ? void 0 : orderSummary.appliedPromotion) == null ? void 0 : _a.code) || "";
  const form = useForm$1({
    resolver: zodResolver(checkoutFormSchema),
    defaultValues: {
      address_id: addresses && addresses.length > 0 ? addresses[0].id.toString() : "",
      payment_method: paymentMethods.length > 0 ? paymentMethods[0] : "cash_on_delivery",
      notes: "",
      coupon_code: initialCouponCode
    }
  });
  useEffect(() => {
    var _a2;
    const addressId = form.watch("address_id");
    if (addressId) {
      const appliedCouponCode = ((_a2 = orderSummary == null ? void 0 : orderSummary.appliedPromotion) == null ? void 0 : _a2.code) || "";
      const data = {
        address_id: addressId
      };
      if (appliedCouponCode) {
        data.coupon_code = appliedCouponCode;
      }
      router.reload({
        only: ["orderSummary"],
        data,
        onSuccess: (page) => {
          if (page.props.orderSummary) {
            setOrderSummary(
              page.props.orderSummary
            );
          }
        }
      });
    }
  }, [form.watch("address_id")]);
  const handleApplyCoupon = () => {
    const addressId = form.watch("address_id");
    const couponCode = form.watch("coupon_code");
    if (!addressId) {
      toast2({
        title: t("address_required", "Address Required"),
        description: t(
          "select_address_first",
          "Please select an address first"
        ),
        variant: "destructive"
      });
      return;
    }
    if (!(couponCode == null ? void 0 : couponCode.trim())) {
      toast2({
        title: t("coupon_required", "Coupon Code Required"),
        description: t(
          "enter_coupon_code",
          "Please enter a coupon code"
        ),
        variant: "destructive"
      });
      return;
    }
    setIsApplyingCoupon(true);
    setCouponError(null);
    router.reload({
      only: ["orderSummary"],
      data: {
        address_id: addressId,
        coupon_code: couponCode.trim()
      },
      onFinish: () => setIsApplyingCoupon(false),
      onSuccess: (page) => {
        if (page.props.orderSummary) {
          setOrderSummary(page.props.orderSummary);
          const newOrderSummary = page.props.orderSummary;
          if (!newOrderSummary.appliedPromotion && newOrderSummary.discount === 0) {
            setCouponError(t(
              "invalid_coupon_code",
              "Invalid coupon code. Please check and try again."
            ));
          } else {
            setCouponError(null);
          }
        }
      },
      onError: (errors) => {
        if (errors.coupon_code) {
          setCouponError(errors.coupon_code);
        } else {
          setCouponError(t(
            "coupon_error",
            "Failed to apply coupon code. Please try again."
          ));
        }
      }
    });
  };
  const handleRemoveCoupon = () => {
    const addressId = form.watch("address_id");
    if (!addressId) return;
    setIsApplyingCoupon(true);
    setCouponError(null);
    form.setValue("coupon_code", "");
    router.reload({
      only: ["orderSummary"],
      data: { address_id: addressId, coupon_code: null },
      onFinish: () => setIsApplyingCoupon(false),
      onSuccess: (page) => {
        if (page.props.orderSummary) {
          setOrderSummary(page.props.orderSummary);
        }
      }
    });
  };
  const handleClearCouponError = () => {
    if (couponError) {
      setCouponError(null);
    }
  };
  const subtotal2 = orderSummary ? orderSummary.subtotal : cartSummary.totalPrice;
  const shippingCost = Number(orderSummary ? orderSummary.shippingCost : 0);
  const discount2 = orderSummary ? orderSummary.discount : 0;
  const total2 = subtotal2 + shippingCost - discount2;
  const appliedPromotionFromBackend = orderSummary == null ? void 0 : orderSummary.appliedPromotion;
  const onSubmit = (values) => {
    if (!values.address_id) {
      toast2({
        title: t("address_required", "Address is required"),
        description: t(
          "select_address_first",
          "Please select or add a shipping address to continue."
        ),
        variant: "destructive"
      });
      return;
    }
    setIsSubmitting(true);
    router.post(
      route("orders.store"),
      {
        address_id: parseInt(values.address_id),
        payment_method: values.payment_method,
        notes: values.notes || null,
        coupon_code: values.coupon_code || null
      },
      {
        onFinish: () => setIsSubmitting(false)
      }
    );
  };
  return /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
    /* @__PURE__ */ jsx(Head, { title: t("checkout", "Checkout") }),
    /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
      /* @__PURE__ */ jsx(
        PageTitle,
        {
          title: t("checkout", "Checkout"),
          backUrl: route("cart.index"),
          backText: t("back_to_cart", "Back to Cart")
        }
      ),
      /* @__PURE__ */ jsx(
        AddressModal,
        {
          trigger: /* @__PURE__ */ jsxs(Button, { variant: "outline", className: "gap-2", children: [
            /* @__PURE__ */ jsx(Plus, { className: "h-4 w-4" }),
            t("add_new_address", "Add New Address")
          ] }),
          onAddressCreated: (newAddress) => {
            router.reload({
              only: ["addresses"],
              onSuccess: () => {
                form.setValue(
                  "address_id",
                  newAddress.id.toString()
                );
              }
            });
          }
        }
      ),
      /* @__PURE__ */ jsx(Form, { ...form, children: /* @__PURE__ */ jsxs(
        "form",
        {
          onSubmit: form.handleSubmit(onSubmit),
          className: "grid gap-6 md:grid-cols-3 items-start",
          children: [
            /* @__PURE__ */ jsx(
              CheckoutForm,
              {
                addresses,
                paymentMethods,
                control: form.control,
                direction
              }
            ),
            /* @__PURE__ */ jsx(
              OrderSummary$1,
              {
                cartSummary,
                subtotal: subtotal2,
                shippingCost,
                discount: discount2,
                total: total2,
                appliedPromotion: appliedPromotionFromBackend,
                selectedAddressId: form.watch("address_id"),
                control: form.control,
                watch: form.watch,
                isSubmitting,
                isApplyingCoupon,
                onApplyCoupon: handleApplyCoupon,
                onRemoveCoupon: handleRemoveCoupon,
                addressesLength: addresses.length,
                couponError,
                onCouponCodeInput: handleClearCouponError
              }
            )
          ]
        }
      ) })
    ] })
  ] });
}
const __vite_glob_0_9 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$2
}, Symbol.toStringTag, { value: "Module" }));
function AnnouncementBanner({ announcements }) {
  const { getLocalizedField } = useI18n();
  const [currentAnnouncementIndex, setCurrentAnnouncementIndex] = useState(0);
  const [isVisible, setIsVisible] = useState(true);
  useEffect(() => {
    if (!announcements || announcements.length === 0) return;
    const interval = setInterval(() => {
      setCurrentAnnouncementIndex(
        (prev) => prev === announcements.length - 1 ? 0 : prev + 1
      );
    }, 6e3);
    return () => clearInterval(interval);
  }, [announcements]);
  if (!announcements || announcements.length === 0 || !isVisible) {
    return null;
  }
  const currentAnnouncement = announcements[currentAnnouncementIndex];
  return /* @__PURE__ */ jsx(AnimatePresence, { children: isVisible && /* @__PURE__ */ jsxs(
    motion.div,
    {
      initial: { height: 0, opacity: 0 },
      animate: { height: "auto", opacity: 1 },
      exit: { height: 0, opacity: 0 },
      transition: { duration: 0.5, ease: "easeInOut" },
      className: "relative rounded-xl overflow-hidden bg-gradient-to-r from-emerald-500 via-emerald-600 to-emerald-500 bg-size-200 animate-gradient-x",
      children: [
        /* @__PURE__ */ jsxs("div", { className: "absolute inset-0 overflow-hidden", children: [
          /* @__PURE__ */ jsx(
            motion.div,
            {
              className: "absolute top-2 left-1/4 w-1 h-1 bg-white/30 rounded-full",
              animate: {
                y: [0, -8, 0],
                opacity: [0.3, 0.8, 0.3],
                scale: [1, 1.2, 1]
              },
              transition: {
                duration: 3,
                repeat: Infinity,
                ease: "easeInOut"
              }
            }
          ),
          /* @__PURE__ */ jsx(
            motion.div,
            {
              className: "absolute top-3 right-1/3 w-1.5 h-1.5 bg-white/40 rounded-full",
              animate: {
                y: [0, -10, 0],
                opacity: [0.4, 0.9, 0.4],
                scale: [1, 1.3, 1]
              },
              transition: {
                duration: 4,
                repeat: Infinity,
                ease: "easeInOut",
                delay: 1
              }
            }
          ),
          /* @__PURE__ */ jsx(
            motion.div,
            {
              className: "absolute bottom-2 left-1/3 w-1 h-1 bg-white/25 rounded-full",
              animate: {
                y: [0, -6, 0],
                opacity: [0.25, 0.7, 0.25],
                scale: [1, 1.1, 1]
              },
              transition: {
                duration: 2.5,
                repeat: Infinity,
                ease: "easeInOut",
                delay: 2
              }
            }
          ),
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent animate-pulse" })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "container mx-auto px-4 py-3 relative", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
            /* @__PURE__ */ jsx(
              motion.div,
              {
                initial: { scale: 0, rotate: -180 },
                animate: { scale: 1, rotate: 0 },
                transition: {
                  duration: 0.6,
                  type: "spring",
                  stiffness: 200,
                  damping: 15
                },
                className: "flex items-center",
                children: /* @__PURE__ */ jsx(
                  motion.div,
                  {
                    animate: {
                      rotate: [0, 5, -5, 0],
                      scale: [1, 1.1, 1]
                    },
                    transition: {
                      duration: 2,
                      repeat: Infinity,
                      ease: "easeInOut"
                    },
                    children: /* @__PURE__ */ jsx(Megaphone, { className: "h-5 w-5 text-white/90 mr-3" })
                  }
                )
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "flex-1 flex items-center justify-center", children: /* @__PURE__ */ jsx(
              motion.div,
              {
                initial: {
                  opacity: 0,
                  y: 20,
                  scale: 0.9,
                  rotateX: 30
                },
                animate: {
                  opacity: 1,
                  y: 0,
                  scale: 1,
                  rotateX: 0
                },
                exit: {
                  opacity: 0,
                  y: -20,
                  scale: 1.1,
                  rotateX: -30
                },
                transition: {
                  duration: 0.7,
                  type: "spring",
                  stiffness: 120,
                  damping: 12
                },
                className: "text-center",
                children: /* @__PURE__ */ jsx(
                  motion.p,
                  {
                    className: "text-white font-semibold text-sm md:text-base tracking-wide",
                    animate: {
                      textShadow: [
                        "0 0 0px rgba(255,255,255,0)",
                        "0 0 8px rgba(255,255,255,0.3)",
                        "0 0 0px rgba(255,255,255,0)"
                      ]
                    },
                    transition: {
                      duration: 3,
                      repeat: Infinity,
                      ease: "easeInOut"
                    },
                    children: getLocalizedField(currentAnnouncement, "title")
                  }
                )
              },
              currentAnnouncement.id
            ) }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center space-x-2", children: [
              /* @__PURE__ */ jsx(
                motion.div,
                {
                  animate: {
                    rotate: [0, 360],
                    scale: [1, 1.2, 1]
                  },
                  transition: {
                    duration: 4,
                    repeat: Infinity,
                    ease: "linear"
                  },
                  children: /* @__PURE__ */ jsx(Sparkles, { className: "h-4 w-4 text-yellow-200/80" })
                }
              ),
              /* @__PURE__ */ jsx(
                motion.button,
                {
                  onClick: () => setIsVisible(false),
                  className: "p-1 rounded-full hover:bg-white/20 transition-colors duration-200",
                  whileHover: {
                    scale: 1.1,
                    rotate: 90
                  },
                  whileTap: { scale: 0.9 },
                  initial: { opacity: 0, x: 20 },
                  animate: { opacity: 1, x: 0 },
                  transition: { delay: 0.5 },
                  children: /* @__PURE__ */ jsx(X, { className: "h-4 w-4 text-white/80 hover:text-white" })
                }
              )
            ] })
          ] }),
          announcements.length > 1 && /* @__PURE__ */ jsx(
            motion.div,
            {
              className: "flex justify-center space-x-2 mt-2",
              initial: { opacity: 0, y: 10 },
              animate: { opacity: 1, y: 0 },
              transition: { delay: 0.8 },
              children: announcements.map((_, index) => /* @__PURE__ */ jsx(
                motion.div,
                {
                  className: `h-1 rounded-full transition-all duration-300 ${index === currentAnnouncementIndex ? "w-8 bg-white/90" : "w-3 bg-white/40"}`,
                  animate: {
                    scale: index === currentAnnouncementIndex ? [1, 1.2, 1] : 1
                  },
                  transition: {
                    duration: 0.5,
                    repeat: index === currentAnnouncementIndex ? Infinity : 0,
                    repeatDelay: 2
                  }
                },
                index
              ))
            }
          )
        ] }),
        /* @__PURE__ */ jsx("div", { className: "absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent" })
      ]
    }
  ) });
}
const AnimatedBackground = () => {
  const [mounted, setMounted] = useState(false);
  useEffect(() => {
    setMounted(true);
  }, []);
  if (!mounted) return null;
  return /* @__PURE__ */ jsxs("div", { className: "absolute inset-0 overflow-hidden pointer-events-none", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-br from-emerald-100/30 via-transparent to-emerald-200/20" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-20 left-10 w-16 h-8 bg-gradient-to-r from-emerald-300/40 to-emerald-400/30 rounded-full blur-sm animate-[swim_8s_ease-in-out_infinite] transform rotate-12" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-32 left-20 w-12 h-6 bg-gradient-to-r from-emerald-400/50 to-emerald-300/40 rounded-full blur-sm animate-[swim_6s_ease-in-out_infinite_reverse] transform -rotate-6" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-60 right-32 w-20 h-10 bg-gradient-to-r from-emerald-300/35 to-emerald-200/30 rounded-full blur-sm animate-[swim_10s_ease-in-out_infinite] transform rotate-45" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-40 left-16 w-14 h-7 bg-gradient-to-r from-emerald-400/45 to-emerald-300/35 rounded-full blur-sm animate-[swim_7s_ease-in-out_infinite_reverse] transform -rotate-12" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-60 right-20 w-18 h-9 bg-gradient-to-r from-emerald-300/40 to-emerald-400/30 rounded-full blur-sm animate-[swim_9s_ease-in-out_infinite] transform rotate-24" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-80 left-32 w-10 h-5 bg-gradient-to-r from-emerald-400/35 to-emerald-300/25 rounded-full blur-sm animate-[swim_5s_ease-in-out_infinite] transform rotate-30" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-80 right-40 w-22 h-11 bg-gradient-to-r from-emerald-300/30 to-emerald-400/20 rounded-full blur-sm animate-[swim_11s_ease-in-out_infinite_reverse] transform -rotate-15" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-96 right-16 w-15 h-8 bg-gradient-to-r from-emerald-400/40 to-emerald-300/30 rounded-full blur-sm animate-[swim_4s_ease-in-out_infinite] transform rotate-60" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-16 right-16 w-40 h-20 bg-gradient-to-br from-emerald-300/25 to-emerald-400/15 rounded-[50%_30%_70%_40%] blur-lg animate-[float_12s_ease-in-out_infinite] transform rotate-45" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-32 left-8 w-36 h-18 bg-gradient-to-tl from-emerald-400/30 to-emerald-300/20 rounded-[40%_60%_30%_70%] blur-lg animate-[float_15s_ease-in-out_infinite_reverse] transform -rotate-30" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-40 left-1/3 w-32 h-16 bg-gradient-to-br from-emerald-300/22 to-emerald-400/12 rounded-[60%_40%_80%_20%] blur-lg animate-[float_18s_ease-in-out_infinite] transform rotate-75" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-48 right-1/3 w-28 h-14 bg-gradient-to-tl from-emerald-400/28 to-emerald-300/18 rounded-[30%_70%_50%_50%] blur-lg animate-[float_14s_ease-in-out_infinite_reverse] transform -rotate-45" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-32 right-32 w-16 h-16 border-2 border-emerald-300/30 rotate-45 animate-[spin_20s_linear_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-32 left-32 w-12 h-12 border-2 border-emerald-400/25 rotate-12 animate-[spin_15s_linear_infinite_reverse]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-64 left-1/4 w-20 h-20 border-2 border-emerald-300/20 rotate-30 animate-[spin_25s_linear_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-64 right-1/4 w-14 h-14 border-2 border-emerald-400/35 rotate-60 animate-[spin_18s_linear_infinite_reverse]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-1/4 left-1/4 w-2 h-2 bg-emerald-400/50 rounded-full animate-[pulse_3s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-3/4 right-1/4 w-1 h-1 bg-emerald-300/70 rounded-full animate-[pulse_4s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-1/2 left-3/4 w-3 h-3 bg-emerald-400/40 rounded-full animate-[pulse_5s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-1/3 right-1/3 w-2 h-2 bg-emerald-300/60 rounded-full animate-[pulse_6s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-1/3 left-1/3 w-1 h-1 bg-emerald-400/70 rounded-full animate-[pulse_3.5s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-2/3 left-1/5 w-2 h-2 bg-emerald-300/50 rounded-full animate-[pulse_4.5s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsxs("svg", { className: "absolute inset-0 w-full h-full", xmlns: "http://www.w3.org/2000/svg", children: [
      /* @__PURE__ */ jsx("defs", { children: /* @__PURE__ */ jsxs("linearGradient", { id: "lineGradient", x1: "0%", y1: "0%", x2: "100%", y2: "100%", children: [
        /* @__PURE__ */ jsx("stop", { offset: "0%", stopColor: "rgb(52 211 153)", stopOpacity: "0.2" }),
        /* @__PURE__ */ jsx("stop", { offset: "100%", stopColor: "rgb(110 231 183)", stopOpacity: "0.1" })
      ] }) }),
      /* @__PURE__ */ jsx(
        "path",
        {
          d: "M 0,200 Q 400,100 800,300 T 1600,200",
          stroke: "url(#lineGradient)",
          strokeWidth: "2",
          fill: "none",
          className: "animate-[drawLine_8s_ease-in-out_infinite]"
        }
      ),
      /* @__PURE__ */ jsx(
        "path",
        {
          d: "M 0,400 Q 200,300 600,500 T 1600,400",
          stroke: "url(#lineGradient)",
          strokeWidth: "1",
          fill: "none",
          className: "animate-[drawLine_10s_ease-in-out_infinite_reverse]"
        }
      )
    ] }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-40 right-20 w-8 h-8 bg-emerald-300/20 rounded-full animate-[drift_12s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-40 left-40 w-6 h-6 bg-emerald-400/25 rounded-full animate-[drift_15s_ease-in-out_infinite_reverse]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-72 left-20 w-10 h-10 bg-emerald-300/15 rounded-full animate-[drift_18s_ease-in-out_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute top-48 right-48 w-12 h-12 border border-emerald-300/25 rounded-full animate-[spin_30s_linear_infinite]" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-48 left-48 w-8 h-8 border border-emerald-400/30 rounded-full animate-[spin_25s_linear_infinite_reverse]" })
  ] });
};
const CarouselContext = React.createContext(null);
function useCarousel() {
  const context = React.useContext(CarouselContext);
  if (!context) {
    throw new Error("useCarousel must be used within a <Carousel />");
  }
  return context;
}
const Carousel = React.forwardRef(
  ({
    orientation = "horizontal",
    opts,
    setApi,
    plugins,
    className,
    children,
    ...props
  }, ref) => {
    const [carouselRef, api] = useEmblaCarousel(
      {
        ...opts,
        axis: orientation === "horizontal" ? "x" : "y"
      },
      plugins
    );
    const [canScrollPrev, setCanScrollPrev] = React.useState(false);
    const [canScrollNext, setCanScrollNext] = React.useState(false);
    const onSelect = React.useCallback((api2) => {
      if (!api2) {
        return;
      }
      setCanScrollPrev(api2.canScrollPrev());
      setCanScrollNext(api2.canScrollNext());
    }, []);
    const scrollPrev = React.useCallback(() => {
      api == null ? void 0 : api.scrollPrev();
    }, [api]);
    const scrollNext = React.useCallback(() => {
      api == null ? void 0 : api.scrollNext();
    }, [api]);
    const handleKeyDown = React.useCallback(
      (event) => {
        if (event.key === "ArrowLeft") {
          event.preventDefault();
          scrollPrev();
        } else if (event.key === "ArrowRight") {
          event.preventDefault();
          scrollNext();
        }
      },
      [scrollPrev, scrollNext]
    );
    React.useEffect(() => {
      if (!api || !setApi) {
        return;
      }
      setApi(api);
    }, [api, setApi]);
    React.useEffect(() => {
      if (!api) {
        return;
      }
      onSelect(api);
      api.on("reInit", onSelect);
      api.on("select", onSelect);
      return () => {
        api == null ? void 0 : api.off("select", onSelect);
      };
    }, [api, onSelect]);
    return /* @__PURE__ */ jsx(
      CarouselContext.Provider,
      {
        value: {
          carouselRef,
          api,
          opts,
          orientation: orientation || ((opts == null ? void 0 : opts.axis) === "y" ? "vertical" : "horizontal"),
          scrollPrev,
          scrollNext,
          canScrollPrev,
          canScrollNext
        },
        children: /* @__PURE__ */ jsx(
          "div",
          {
            ref,
            onKeyDownCapture: handleKeyDown,
            className: cn("relative", className),
            role: "region",
            "aria-roledescription": "carousel",
            ...props,
            children
          }
        )
      }
    );
  }
);
Carousel.displayName = "Carousel";
const CarouselContent = React.forwardRef(({ className, ...props }, ref) => {
  const { carouselRef, orientation } = useCarousel();
  return /* @__PURE__ */ jsx("div", { ref: carouselRef, className: "overflow-hidden", children: /* @__PURE__ */ jsx(
    "div",
    {
      ref,
      className: cn(
        "flex",
        orientation === "horizontal" ? "-ml-4" : "-mt-4 flex-col",
        className
      ),
      ...props
    }
  ) });
});
CarouselContent.displayName = "CarouselContent";
const CarouselItem = React.forwardRef(({ className, ...props }, ref) => {
  const { orientation } = useCarousel();
  return /* @__PURE__ */ jsx(
    "div",
    {
      ref,
      role: "group",
      "aria-roledescription": "slide",
      className: cn(
        "min-w-0 shrink-0 grow-0 basis-full",
        orientation === "horizontal" ? "pl-4" : "pt-4",
        className
      ),
      ...props
    }
  );
});
CarouselItem.displayName = "CarouselItem";
const CarouselPrevious = React.forwardRef(({ className, variant: variant2 = "outline", size: size2 = "icon", ...props }, ref) => {
  const { orientation, scrollPrev, canScrollPrev } = useCarousel();
  return /* @__PURE__ */ jsxs(
    Button,
    {
      ref,
      variant: variant2,
      size: size2,
      className: cn(
        "absolute  h-8 w-8 rounded-full",
        orientation === "horizontal" ? "-left-12 top-1/2 -translate-y-1/2" : "-top-12 left-1/2 -translate-x-1/2 rotate-90",
        className
      ),
      disabled: !canScrollPrev,
      onClick: scrollPrev,
      ...props,
      children: [
        /* @__PURE__ */ jsx(ArrowLeft, { className: "h-4 w-4" }),
        /* @__PURE__ */ jsx("span", { className: "sr-only", children: "Previous slide" })
      ]
    }
  );
});
CarouselPrevious.displayName = "CarouselPrevious";
const CarouselNext = React.forwardRef(({ className, variant: variant2 = "outline", size: size2 = "icon", ...props }, ref) => {
  const { orientation, scrollNext, canScrollNext } = useCarousel();
  return /* @__PURE__ */ jsxs(
    Button,
    {
      ref,
      variant: variant2,
      size: size2,
      className: cn(
        "absolute h-8 w-8 rounded-full",
        orientation === "horizontal" ? "-right-12 top-1/2 -translate-y-1/2" : "-bottom-12 left-1/2 -translate-x-1/2 rotate-90",
        className
      ),
      disabled: !canScrollNext,
      onClick: scrollNext,
      ...props,
      children: [
        /* @__PURE__ */ jsx(ArrowRight, { className: "h-4 w-4" }),
        /* @__PURE__ */ jsx("span", { className: "sr-only", children: "Next slide" })
      ]
    }
  );
});
CarouselNext.displayName = "CarouselNext";
function HeroCarousel({ heroSlides }) {
  useI18n();
  const [api, setApi] = useState();
  const [current, setCurrent] = useState(0);
  const [animationKey, setAnimationKey] = useState(0);
  const onSlideChange = useCallback(() => {
    if (!api) return;
    const newCurrent = api.selectedScrollSnap();
    setCurrent(newCurrent);
    setAnimationKey((prev) => prev + 1);
  }, [api]);
  useEffect(() => {
    if (!api) return;
    api.on("select", onSlideChange);
    return () => {
      api.off("select", onSlideChange);
    };
  }, [api, onSlideChange]);
  if (!heroSlides || heroSlides.length === 0) {
    return null;
  }
  return /* @__PURE__ */ jsx(
    motion.div,
    {
      initial: { opacity: 0, y: 20 },
      animate: { opacity: 1, y: 0 },
      transition: { duration: 0.8 },
      children: /* @__PURE__ */ jsxs(
        Carousel,
        {
          setApi,
          className: "w-full my-4 force-ltr",
          plugins: [
            Autoplay({
              delay: 3e3
            })
          ],
          opts: { loop: true },
          children: [
            /* @__PURE__ */ jsx(CarouselContent, { children: heroSlides.map((slide, index) => /* @__PURE__ */ jsx(CarouselItem, { className: "cursor-pointer", children: /* @__PURE__ */ jsx(
              "img",
              {
                src: "storage/" + slide.image,
                alt: "Nike Electric Shoe",
                className: "w-full rounded-3xl h-auto "
              }
            ) }, slide.id)) }),
            /* @__PURE__ */ jsx("div", { className: "flex justify-center space-x-2 mt-4", children: heroSlides.map((_, index) => /* @__PURE__ */ jsx(
              motion.button,
              {
                className: `w-2 h-2 rounded-full transition-all duration-300 ${index === current ? "bg-primary w-8 shadow-lg shadow-primary/40" : "bg-primary/30 hover:bg-primary/60"}`,
                onClick: () => api == null ? void 0 : api.scrollTo(index),
                whileHover: { scale: 1.3, y: -2 },
                whileTap: { scale: 0.8 },
                initial: { opacity: 0, y: 20, rotateX: 90 },
                animate: {
                  opacity: 1,
                  y: 0,
                  rotateX: 0,
                  scale: index === current ? 1.2 : 1
                },
                transition: {
                  duration: 0.4,
                  delay: index * 0.1
                }
              },
              index
            )) })
          ]
        }
      )
    }
  );
}
function ProductCard({ product }) {
  const { getLocalizedField, t } = useI18n();
  const { addToCart, addingToCart } = useCart();
  const hasDiscount = product.sale_price && product.sale_price !== product.price;
  const discountPercentage = hasDiscount ? Math.round(
    (product.price - product.sale_price) / product.price * 100
  ) : 0;
  return /* @__PURE__ */ jsxs(Card, { className: "group overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 h-full flex flex-col border-0 shadow-sm bg-card/50 backdrop-blur-sm", children: [
    /* @__PURE__ */ jsxs(
      Link,
      {
        href: `/products/${product.id}`,
        className: "flex-1 flex flex-col",
        children: [
          /* @__PURE__ */ jsxs("div", { className: "aspect-[1/1] relative bg-gradient-to-br from-muted/30 to-muted/60 overflow-hidden", children: [
            /* @__PURE__ */ jsx(
              Image,
              {
                src: product.featured_image || "/placeholder.jpg",
                alt: getLocalizedField(product, "name"),
                className: "object-contain w-full h-full aspect-square transition-transform duration-500 group-hover:scale-110"
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" }),
            hasDiscount && discountPercentage > 0 && /* @__PURE__ */ jsxs("div", { className: "absolute top-3 right-3 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg animate-pulse", children: [
              "-",
              discountPercentage,
              "%"
            ] }),
            product.quantity <= 0 && /* @__PURE__ */ jsx("div", { className: "absolute top-3 left-3 bg-gray-900/90 text-white text-xs font-medium px-3 py-1.5 rounded-full", children: t("out_of_stock", "Out of Stock") })
          ] }),
          /* @__PURE__ */ jsxs(CardContent, { className: "p-5 flex-1 space-y-3", children: [
            product.brand && /* @__PURE__ */ jsx("div", { className: "flex items-center", children: /* @__PURE__ */ jsx("span", { className: "text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded-full", children: getLocalizedField(product.brand, "name") }) }),
            /* @__PURE__ */ jsx("h3", { className: "font-semibold text-lg leading-tight line-clamp-2 group-hover:text-primary transition-colors duration-200", children: getLocalizedField(product, "name") }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mt-auto pt-3", children: [
              /* @__PURE__ */ jsx("div", { className: "flex items-center gap-2", children: hasDiscount ? /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
                /* @__PURE__ */ jsxs("span", { className: "text-xl font-bold text-primary", children: [
                  Number(product.sale_price).toFixed(2),
                  " EGP"
                ] }),
                /* @__PURE__ */ jsxs("span", { className: "text-muted-foreground text-sm line-through", children: [
                  Number(product.price).toFixed(2),
                  " EGP"
                ] })
              ] }) : /* @__PURE__ */ jsxs("span", { className: "text-xl font-bold text-foreground", children: [
                Number(product.price).toFixed(2),
                " EGP"
              ] }) }),
              /* @__PURE__ */ jsx(
                Button,
                {
                  variant: "ghost",
                  size: "icon",
                  className: "rounded-full h-10 w-10 bg-primary/10 hover:bg-primary hover:text-primary-foreground transition-all duration-200 opacity-0 group-hover:opacity-100",
                  onClick: (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    addToCart(product.id, 1);
                  },
                  disabled: addingToCart[product.id] || product.quantity <= 0,
                  children: /* @__PURE__ */ jsx(ShoppingBag, { className: "h-4 w-4" })
                }
              )
            ] })
          ] })
        ]
      }
    ),
    /* @__PURE__ */ jsx(CardFooter, { className: "p-5 pt-0", children: /* @__PURE__ */ jsxs(
      Button,
      {
        variant: product.quantity <= 0 ? "secondary" : "default",
        className: "w-full font-medium transition-all duration-200 hover:shadow-md",
        size: "lg",
        onClick: () => addToCart(product.id, 1),
        disabled: addingToCart[product.id] || product.quantity <= 0,
        children: [
          /* @__PURE__ */ jsx(ShoppingBag, { className: "h-4 w-4 mr-2" }),
          addingToCart[product.id] ? t("adding", "Adding...") : product.quantity <= 0 ? t("out_of_stock", "Out of Stock") : t("add_to_cart", "Add to Cart")
        ]
      }
    ) })
  ] });
}
function Skeleton({
  className,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    "div",
    {
      className: cn("animate-pulse rounded-md bg-primary/10", className),
      ...props
    }
  );
}
function ProductGrid({
  title,
  viewAllLink,
  emptyMessage,
  className = "",
  sectionId,
  viewType = "scroll"
  // Default to horizontal scrolling view
}) {
  const { t, direction } = useI18n();
  const page = usePage();
  const sectionKey = `section_${sectionId}_page`;
  const actualDataKey = `${sectionKey}_data`;
  const actualPaginationKey = `${sectionKey}_pagination`;
  const products = page.props[actualDataKey];
  console.log(page.props[actualPaginationKey]);
  const pagination = page.props[actualPaginationKey];
  const ProductCardSkeleton = () => /* @__PURE__ */ jsxs(
    "div",
    {
      className: cn(
        "space-y-3",
        viewType === "scroll" ? "snap-start flex-shrink-0 w-[250px] sm:w-[300px]" : "w-full"
      ),
      children: [
        /* @__PURE__ */ jsx(Skeleton, { className: "w-full h-[180px] rounded-lg" }),
        /* @__PURE__ */ jsx(Skeleton, { className: "w-3/4 h-5 rounded" }),
        /* @__PURE__ */ jsx(Skeleton, { className: "w-1/2 h-4 rounded" }),
        /* @__PURE__ */ jsx(Skeleton, { className: "w-1/4 h-6 rounded" })
      ]
    }
  );
  return /* @__PURE__ */ jsxs("section", { className: cn(`py-12 md:py-16`, className), children: [
    (title || viewAllLink) && /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between pb-8", children: [
      title && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 md:gap-4", children: [
        /* @__PURE__ */ jsx("div", { className: "w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-primary flex items-center justify-center", children: /* @__PURE__ */ jsx(PackageOpen, { className: "h-4 w-4 sm:h-5 sm:w-5 text-white" }) }),
        /* @__PURE__ */ jsx("h2", { className: "text-2xl md:text-3xl font-bold", children: t(title) })
      ] }),
      viewAllLink && /* @__PURE__ */ jsxs(
        Link,
        {
          href: viewAllLink,
          className: "inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors group",
          children: [
            t("view_all", "View All"),
            direction === "rtl" ? /* @__PURE__ */ jsx(ArrowLeft, { className: "h-4 w-4 transition-transform group-hover:-translate-x-1" }) : /* @__PURE__ */ jsx(ArrowRight, { className: "h-4 w-4 transition-transform group-hover:translate-x-1" })
          ]
        }
      )
    ] }),
    products && products.length > 0 ? /* @__PURE__ */ jsxs(
      "div",
      {
        className: cn(
          viewType === "scroll" ? "flex overflow-x-auto pb-4 gap-4 snap-x scrollbar-hide" : "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6"
        ),
        children: [
          products.map((product) => /* @__PURE__ */ jsx(
            "div",
            {
              className: cn(
                viewType === "scroll" ? "snap-start flex-shrink-0 w-[250px] sm:w-[300px]" : ""
              ),
              children: /* @__PURE__ */ jsx(ProductCard, { product })
            },
            product.id
          )),
          pagination && pagination.next_page_url && /* @__PURE__ */ jsx(
            WhenVisible,
            {
              params: {
                only: [actualDataKey, actualPaginationKey],
                data: {
                  [sectionKey]: pagination.current_page + 1
                },
                preserveUrl: true,
                onSuccess: (page2) => {
                  window.history.state.page.props = page2.props;
                }
              },
              always: !!pagination.next_page_url,
              fallback: /* @__PURE__ */ jsx(ProductCardSkeleton, {}),
              children: /* @__PURE__ */ jsx(ProductCardSkeleton, {})
            }
          )
        ]
      }
    ) : /* @__PURE__ */ jsx(
      EmptyState,
      {
        title: emptyMessage || t("no_products_available", "No products available"),
        icon: /* @__PURE__ */ jsx(PackageOpen, { className: "h-8 w-8 text-muted-foreground" })
      }
    )
  ] });
}
function useSettings() {
  const { props } = usePage();
  return props.settings || {
    site_title: "Vilain",
    maintenance_mode: false,
    social_links: "{}"
  };
}
function useSiteBranding() {
  const settings = useSettings();
  return {
    title: settings.site_title || "Vilain",
    logo: settings.site_logo,
    icon: settings.site_icon
  };
}
function Home$1({
  announcements,
  heroSlides,
  categories: categories2,
  brands: brands2,
  sections
}) {
  const { t, getLocalizedField } = useI18n();
  const { title } = useSiteBranding();
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-[#fdfbf7] overflow-hidden min-h-[calc(100vh-65px)]", children: [
      /* @__PURE__ */ jsx(AnimatedBackground, {}),
      /* @__PURE__ */ jsxs("div", { className: "container pt-8", children: [
        /* @__PURE__ */ jsx(AnnouncementBanner, { announcements }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col lg:flex-row items-center justify-between min-h-[calc(100vh-120px)] py-12 lg:py-16", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex-1 lg:pr-12 text-center lg:text-left mb-12 lg:mb-0 font-mono", children: [
            /* @__PURE__ */ jsxs("h1", { className: "text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6", children: [
              "Play with new",
              " ",
              /* @__PURE__ */ jsx(
                "span",
                {
                  className: "text-transparent bg-clip-text ",
                  style: {
                    WebkitTextStroke: "2px #10b981",
                    color: "transparent"
                  },
                  children: "electric"
                }
              ),
              " ",
              "Nike products..."
            ] }),
            /* @__PURE__ */ jsx("p", { className: "text-lg md:text-xl text-gray-600 mb-8 max-w-lg mx-auto lg:mx-0", children: "Find, explore and buy in an awesome place find, explore and buy in great and awesome place an awesome, explore more." }),
            /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row gap-4 justify-center lg:justify-start", children: [
              /* @__PURE__ */ jsxs("button", { className: "bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors duration-200 flex items-center justify-center gap-2", children: [
                "Products",
                /* @__PURE__ */ jsx(
                  "svg",
                  {
                    width: "20",
                    height: "20",
                    viewBox: "0 0 24 24",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg",
                    children: /* @__PURE__ */ jsx(
                      "path",
                      {
                        d: "M5 12H19M19 12L12 5M19 12L12 19",
                        stroke: "currentColor",
                        strokeWidth: "2",
                        strokeLinecap: "round",
                        strokeLinejoin: "round"
                      }
                    )
                  }
                )
              ] }),
              /* @__PURE__ */ jsxs("button", { className: "bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-4 rounded-lg font-semibold text-lg transition-colors duration-200 flex items-center justify-center gap-2", children: [
                "Shoe blog",
                /* @__PURE__ */ jsx(
                  "svg",
                  {
                    width: "20",
                    height: "20",
                    viewBox: "0 0 24 24",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg",
                    children: /* @__PURE__ */ jsx(
                      "path",
                      {
                        d: "M5 12H19M19 12L12 5M19 12L12 19",
                        stroke: "currentColor",
                        strokeWidth: "2",
                        strokeLinecap: "round",
                        strokeLinejoin: "round"
                      }
                    )
                  }
                )
              ] })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "mt-8", children: /* @__PURE__ */ jsxs("p", { className: "text-gray-500 flex items-center justify-center lg:justify-start gap-2", children: [
              /* @__PURE__ */ jsx("span", { className: "w-2 h-2 bg-emerald-400 rounded-full" }),
              "Have any question?"
            ] }) })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "flex-1 relative", children: /* @__PURE__ */ jsx("div", { className: "relative z-10 text-center", children: /* @__PURE__ */ jsx(HeroCarousel, { heroSlides }) }) })
        ] })
      ] })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "container mt-4", children: sections && sections.map((section) => /* @__PURE__ */ jsx(
      ProductGrid,
      {
        sectionId: section.id,
        title: getLocalizedField(section, "title"),
        viewAllLink: `/sections/${section.id}`,
        emptyMessage: t(
          "no_products_available",
          "No products available in this section"
        )
      },
      section.id
    )) })
  ] });
}
const __vite_glob_0_10 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Home$1
}, Symbol.toStringTag, { value: "Module" }));
function ItemGrid({
  title,
  viewAllLink,
  emptyMessage,
  className = "",
  sectionId,
  paginationKey,
  dataKey,
  viewType = "scroll",
  scrollDirection = "horizontal",
  renderItem,
  icon = /* @__PURE__ */ jsx(LayoutGrid, { className: "h-6 w-6 text-primary" }),
  itemWidth = "w-[250px] sm:w-[300px]",
  itemHeight,
  gridCols = {
    default: "grid-cols-2",
    sm: "sm:grid-cols-3",
    lg: "lg:grid-cols-4",
    xl: "xl:grid-cols-5"
  }
}) {
  const { t, direction } = useI18n();
  const page = usePage();
  const sectionKey = `section_${sectionId}_items_page`;
  const actualDataKey = dataKey || `${sectionKey}_data`;
  const actualPaginationKey = paginationKey || `${sectionKey}_pagination`;
  const items2 = page.props[actualDataKey];
  const pagination = page.props[actualPaginationKey];
  const ItemCardSkeleton = () => /* @__PURE__ */ jsxs(
    "div",
    {
      className: cn(
        "space-y-3",
        viewType === "scroll" ? scrollDirection === "horizontal" ? `snap-start flex-shrink-0 ${itemWidth}` : `snap-start w-full ${itemHeight || "h-auto"}` : "w-full"
      ),
      children: [
        /* @__PURE__ */ jsx(Skeleton, { className: "w-full h-[180px] rounded-lg" }),
        /* @__PURE__ */ jsx(Skeleton, { className: "w-3/4 h-5 rounded" }),
        /* @__PURE__ */ jsx(Skeleton, { className: "w-1/2 h-4 rounded" }),
        /* @__PURE__ */ jsx(Skeleton, { className: "w-1/4 h-6 rounded" })
      ]
    }
  );
  return /* @__PURE__ */ jsxs("section", { className: cn(`py-12 md:py-16`, className), children: [
    (title || viewAllLink) && /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between pb-8", children: [
      title && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
        icon,
        /* @__PURE__ */ jsx("h2", { className: "text-2xl md:text-3xl font-bold", children: t(title) })
      ] }),
      viewAllLink && /* @__PURE__ */ jsxs(
        Link,
        {
          href: viewAllLink,
          className: "inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors group",
          children: [
            t("view_all", "View All"),
            direction === "rtl" ? /* @__PURE__ */ jsx(ArrowLeft, { className: "h-4 w-4 transition-transform group-hover:-translate-x-1" }) : /* @__PURE__ */ jsx(ArrowRight, { className: "h-4 w-4 transition-transform group-hover:translate-x-1" })
          ]
        }
      )
    ] }),
    items2 && items2.length > 0 ? /* @__PURE__ */ jsxs(
      "div",
      {
        className: cn(
          viewType === "scroll" ? scrollDirection === "horizontal" ? "flex overflow-x-auto pb-4 gap-4 snap-x scrollbar-hide" : "flex flex-col pb-4 gap-4 snap-y scrollbar-hide max-h-[70vh]" : `grid ${gridCols.default} ${gridCols.sm} ${gridCols.lg} ${gridCols.xl} gap-4 md:gap-6`
        ),
        children: [
          items2.map((item, index) => /* @__PURE__ */ jsx(
            "div",
            {
              className: cn(
                viewType === "scroll" ? scrollDirection === "horizontal" ? `snap-start flex-shrink-0 ${itemWidth}` : `snap-start ${itemHeight || "h-auto"}` : ""
              ),
              children: renderItem(item)
            },
            index
          )),
          pagination && pagination.next_page_url && /* @__PURE__ */ jsx(
            WhenVisible,
            {
              params: {
                only: [actualDataKey, actualPaginationKey],
                data: {
                  [sectionKey]: pagination.current_page + 1
                },
                preserveUrl: true,
                onSuccess: (page2) => {
                  window.history.state.page.props = page2.props;
                }
              },
              always: !!pagination.next_page_url,
              fallback: /* @__PURE__ */ jsx(ItemCardSkeleton, {}),
              children: /* @__PURE__ */ jsx(ItemCardSkeleton, {})
            }
          )
        ]
      }
    ) : /* @__PURE__ */ jsx(
      EmptyState,
      {
        title: emptyMessage || t("no_items_available", "No items available"),
        icon
      }
    )
  ] });
}
function Index$1() {
  const { t, getLocalizedField } = useI18n();
  const getStatusBadgeColor = (status) => {
    switch (status) {
      case "processing":
        return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
      case "shipped":
        return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500";
      case "delivered":
        return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
      case "cancelled":
        return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500";
      default:
        return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
    }
  };
  const getPaymentStatusBadgeColor = (status) => {
    switch (status) {
      case "paid":
        return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
      case "pending":
        return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
      default:
        return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
    }
  };
  const needsPayment = (order) => {
    return order.payment_method !== "cash_on_delivery" && order.payment_status === "pending" && order.order_status !== "cancelled";
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("my_orders", "My Orders") }),
    /* @__PURE__ */ jsx("div", { className: "container mt-4", children: /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
      /* @__PURE__ */ jsx(
        PageTitle,
        {
          title: t("my_orders", "My Orders"),
          icon: /* @__PURE__ */ jsx(ShoppingBag, { className: "h-6 w-6 text-primary" })
        }
      ),
      /* @__PURE__ */ jsx(
        ItemGrid,
        {
          className: "py-0",
          sectionId: "orders",
          dataKey: "orders_data",
          paginationKey: "orders_pagination",
          viewType: "scroll",
          scrollDirection: "vertical",
          renderItem: (order) => {
            var _a;
            return /* @__PURE__ */ jsxs(Card, { className: "overflow-hidden", children: [
              /* @__PURE__ */ jsx(CardHeader, { className: "bg-muted/30 pb-3", children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2", children: [
                /* @__PURE__ */ jsxs(CardTitle, { className: "text-base font-medium", children: [
                  t("order_number", "Order"),
                  " #",
                  order.id
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 text-sm text-muted-foreground flex-wrap", children: [
                  /* @__PURE__ */ jsx("span", { children: formatDate(order.created_at) }),
                  /* @__PURE__ */ jsx("span", { className: "hidden sm:inline", children: "•" }),
                  /* @__PURE__ */ jsx(
                    Badge,
                    {
                      variant: "outline",
                      className: getStatusBadgeColor(
                        order.order_status
                      ),
                      children: t(
                        `order_status_${order.order_status}`,
                        order.order_status
                      )
                    }
                  ),
                  /* @__PURE__ */ jsx(
                    Badge,
                    {
                      variant: "outline",
                      className: getPaymentStatusBadgeColor(
                        order.payment_status
                      ),
                      children: t(
                        `payment_status_${order.payment_status}`,
                        order.payment_status
                      )
                    }
                  )
                ] })
              ] }) }),
              /* @__PURE__ */ jsx(CardContent, { className: "pt-4", children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row justify-between gap-4", children: [
                /* @__PURE__ */ jsxs("div", { className: "space-y-1", children: [
                  /* @__PURE__ */ jsx("p", { className: "text-sm font-medium", children: t("items", "Items") }),
                  /* @__PURE__ */ jsxs("p", { className: "text-sm text-muted-foreground", children: [
                    ((_a = order.items) == null ? void 0 : _a.length) || 0,
                    " ",
                    t("items", "items")
                  ] })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "space-y-1", children: [
                  /* @__PURE__ */ jsx("p", { className: "text-sm font-medium", children: t("total", "Total") }),
                  /* @__PURE__ */ jsxs("p", { className: "text-sm", children: [
                    "EGP ",
                    Number(order.total).toFixed(2)
                  ] })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "space-y-1", children: [
                  /* @__PURE__ */ jsx("p", { className: "text-sm font-medium", children: t(
                    "payment_method",
                    "Payment Method"
                  ) }),
                  /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t(
                    `payment_method_${order.payment_method}`,
                    order.payment_method === "cash_on_delivery" ? "Cash on Delivery" : order.payment_method
                  ) })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
                  needsPayment(order) && /* @__PURE__ */ jsx(
                    Button,
                    {
                      asChild: true,
                      className: "flex items-center gap-1",
                      variant: "default",
                      children: /* @__PURE__ */ jsxs(
                        Link,
                        {
                          href: route(
                            "kashier.payment.show",
                            order.id
                          ),
                          children: [
                            /* @__PURE__ */ jsx(CreditCard, { className: "h-4 w-4" }),
                            t(
                              "complete_payment",
                              "Complete Payment"
                            )
                          ]
                        }
                      )
                    }
                  ),
                  /* @__PURE__ */ jsx(Button, { asChild: true, variant: "outline", children: /* @__PURE__ */ jsx(
                    Link,
                    {
                      href: route(
                        "orders.show",
                        order.id
                      ),
                      children: t(
                        "view_details",
                        "View Details"
                      )
                    }
                  ) })
                ] })
              ] }) })
            ] }, order.id);
          }
        }
      )
    ] }) })
  ] });
}
const __vite_glob_0_11 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$1
}, Symbol.toStringTag, { value: "Module" }));
function ReturnHistory({ returnHistory }) {
  const { t, getLocalizedField } = useI18n();
  const getReturnStatusBadgeColor = (status) => {
    switch (status) {
      case "return_requested":
        return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
      case "return_approved":
        return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500";
      case "return_rejected":
        return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500";
      case "item_returned":
      case "refund_processed":
        return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
      default:
        return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
    }
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("return_history", "Return History") }),
    /* @__PURE__ */ jsx("div", { className: "container mt-4", children: /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
        /* @__PURE__ */ jsx(
          PageTitle,
          {
            title: t("return_history", "Return History"),
            icon: /* @__PURE__ */ jsx(RotateCcw, { className: "h-6 w-6 text-primary" })
          }
        ),
        /* @__PURE__ */ jsx(Link, { href: route("orders.index"), children: /* @__PURE__ */ jsxs(Button, { variant: "outline", className: "flex items-center gap-2", children: [
          /* @__PURE__ */ jsx(ArrowLeft, { className: "h-4 w-4" }),
          t("back_to_orders", "Back to Orders")
        ] }) })
      ] }),
      returnHistory.length === 0 ? /* @__PURE__ */ jsx(Card, { children: /* @__PURE__ */ jsxs(CardContent, { className: "flex flex-col items-center justify-center py-12", children: [
        /* @__PURE__ */ jsx(Package, { className: "h-16 w-16 text-muted-foreground mb-4" }),
        /* @__PURE__ */ jsx("h2", { className: "text-xl font-semibold text-muted-foreground mb-2", children: t("no_returns", "No Return Requests") }),
        /* @__PURE__ */ jsx("p", { className: "text-muted-foreground text-center", children: t("no_returns_message", "You haven't requested any returns yet") }),
        /* @__PURE__ */ jsx(Link, { href: route("orders.index"), className: "mt-4", children: /* @__PURE__ */ jsx(Button, { children: t("view_orders", "View Orders") }) })
      ] }) }) : /* @__PURE__ */ jsx("div", { className: "space-y-4", children: returnHistory.map((returnItem) => {
        var _a;
        return /* @__PURE__ */ jsxs(Card, { className: "overflow-hidden", children: [
          /* @__PURE__ */ jsx(CardHeader, { className: "bg-muted/30 pb-3", children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2", children: [
            /* @__PURE__ */ jsxs(CardTitle, { className: "text-base font-medium flex items-center gap-2", children: [
              /* @__PURE__ */ jsx(Package, { className: "h-5 w-5" }),
              t("order_number", "Order"),
              " #",
              returnItem.id
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 text-sm text-muted-foreground flex-wrap", children: [
              /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-1", children: [
                /* @__PURE__ */ jsx(CalendarDays, { className: "h-4 w-4" }),
                formatDate(returnItem.return_requested_at)
              ] }),
              /* @__PURE__ */ jsx("span", { className: "hidden sm:inline", children: "•" }),
              /* @__PURE__ */ jsx(
                Badge,
                {
                  variant: "outline",
                  className: getReturnStatusBadgeColor(returnItem.return_status),
                  children: t(`return_status_${returnItem.return_status}`, returnItem.return_status)
                }
              )
            ] })
          ] }) }),
          /* @__PURE__ */ jsx(CardContent, { className: "pt-4", children: /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row justify-between gap-4", children: [
              /* @__PURE__ */ jsxs("div", { className: "space-y-1", children: [
                /* @__PURE__ */ jsx("p", { className: "text-sm font-medium", children: t("items", "Items") }),
                /* @__PURE__ */ jsxs("p", { className: "text-sm text-muted-foreground", children: [
                  ((_a = returnItem.items) == null ? void 0 : _a.length) || 0,
                  " ",
                  t("items", "items")
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "space-y-1", children: [
                /* @__PURE__ */ jsx("p", { className: "text-sm font-medium", children: t("total_amount", "Total Amount") }),
                /* @__PURE__ */ jsxs("p", { className: "text-sm", children: [
                  "EGP ",
                  Number(returnItem.total).toFixed(2)
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("h4", { className: "text-sm font-medium mb-2", children: [
                t("return_reason", "Return Reason"),
                ":"
              ] }),
              /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground bg-muted/50 p-3 rounded-lg", children: returnItem.return_reason })
            ] }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("h4", { className: "text-sm font-medium mb-3", children: [
                t("returned_products", "Returned Products"),
                ":"
              ] }),
              /* @__PURE__ */ jsx("div", { className: "space-y-2", children: returnItem.items.map((item) => /* @__PURE__ */ jsxs(
                "div",
                {
                  className: "flex items-center justify-between p-3 bg-muted/50 rounded-lg",
                  children: [
                    /* @__PURE__ */ jsxs("div", { className: "flex-1", children: [
                      /* @__PURE__ */ jsx("h5", { className: "text-sm font-medium", children: getLocalizedField(item.product, "name") }),
                      item.variant && /* @__PURE__ */ jsxs("div", { className: "text-xs text-muted-foreground mt-1 space-x-2", children: [
                        item.variant.color && /* @__PURE__ */ jsxs("span", { children: [
                          t("color", "Color"),
                          ": ",
                          item.variant.color
                        ] }),
                        item.variant.size && /* @__PURE__ */ jsxs("span", { children: [
                          t("size", "Size"),
                          ": ",
                          item.variant.size
                        ] }),
                        item.variant.capacity && /* @__PURE__ */ jsxs("span", { children: [
                          t("capacity", "Capacity"),
                          ": ",
                          item.variant.capacity
                        ] })
                      ] })
                    ] }),
                    /* @__PURE__ */ jsxs("div", { className: "text-right space-y-1", children: [
                      /* @__PURE__ */ jsxs("div", { className: "text-xs text-muted-foreground", children: [
                        t("quantity", "Qty"),
                        ": ",
                        item.quantity
                      ] }),
                      /* @__PURE__ */ jsxs("div", { className: "text-sm font-medium", children: [
                        "EGP ",
                        Number(item.price).toFixed(2)
                      ] })
                    ] })
                  ]
                },
                item.id
              )) })
            ] })
          ] }) })
        ] }, returnItem.id);
      }) })
    ] }) })
  ] });
}
const __vite_glob_0_12 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ReturnHistory
}, Symbol.toStringTag, { value: "Module" }));
const Progress = React.forwardRef(({ className, value, ...props }, ref) => /* @__PURE__ */ jsx(
  ProgressPrimitive.Root,
  {
    ref,
    className: cn(
      "relative h-2 w-full overflow-hidden rounded-full bg-primary/20",
      className
    ),
    ...props,
    children: /* @__PURE__ */ jsx(
      ProgressPrimitive.Indicator,
      {
        className: "h-full w-full flex-1 bg-primary transition-all",
        style: { transform: `translateX(-${100 - (value || 0)}%)` }
      }
    )
  }
));
Progress.displayName = ProgressPrimitive.Root.displayName;
function OrderProgress({ order }) {
  const { t, direction } = useI18n();
  const orderStatuses = ["processing", "shipped", "delivered"];
  const getOrderProgressPercentage = () => {
    if (order.order_status === "cancelled") return 0;
    const currentIndex = orderStatuses.indexOf(
      order.order_status
    );
    if (currentIndex < 0) return 0;
    return (currentIndex + 1) / orderStatuses.length * 100;
  };
  if (order.order_status === "cancelled") {
    return null;
  }
  return /* @__PURE__ */ jsx("div", { className: "rounded-lg border bg-card text-card-foreground shadow-sm overflow-hidden", dir: "ltr", children: /* @__PURE__ */ jsxs("div", { className: "p-6", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-xl font-semibold mb-4", dir: "rtl", children: t("order_progress", "Order Progress") }),
    /* @__PURE__ */ jsx(
      Progress,
      {
        value: getOrderProgressPercentage(),
        className: "h-2 mb-6"
      }
    ),
    /* @__PURE__ */ jsx("div", { className: "grid grid-cols-3 gap-2", children: orderStatuses.map((status, index) => /* @__PURE__ */ jsxs(
      "div",
      {
        className: "flex flex-col items-center text-center",
        children: [
          /* @__PURE__ */ jsx(
            "div",
            {
              className: cn(
                "w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors",
                orderStatuses.indexOf(
                  order.order_status
                ) >= index ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
              ),
              children: index === 0 ? /* @__PURE__ */ jsx(Clock, { className: "w-5 h-5" }) : index === 1 ? /* @__PURE__ */ jsx(Truck, { className: "w-5 h-5" }) : /* @__PURE__ */ jsx(CheckCircle, { className: "w-5 h-5" })
            }
          ),
          /* @__PURE__ */ jsx(
            "span",
            {
              className: cn(
                "text-sm font-medium"
              ),
              children: t(
                `order_status_${status}`,
                status
              )
            }
          )
        ]
      },
      status
    )) })
  ] }) });
}
function OrderCancelledAlert({}) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs("div", { className: "rounded-lg border border-red-200 bg-red-50 dark:bg-red-950/20 dark:border-red-900/50 p-6 flex gap-4 items-center", children: [
    /* @__PURE__ */ jsx("div", { className: "rounded-full bg-red-100 dark:bg-red-900/30 p-3", children: /* @__PURE__ */ jsx(AlertCircle, { className: "w-6 h-6 text-red-600 dark:text-red-400" }) }),
    /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h3", { className: "text-lg font-semibold text-red-800 dark:text-red-400", children: t("order_cancelled", "Order Cancelled") }),
      /* @__PURE__ */ jsx("p", { className: "text-red-700 dark:text-red-300", children: t(
        "order_cancelled_message",
        "This order has been cancelled and will not be processed further."
      ) })
    ] })
  ] });
}
function OrderDetailsCard({ order }) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs(Card, { className: "overflow-hidden border-none shadow-md", children: [
    /* @__PURE__ */ jsx(CardHeader, { className: "pb-3 bg-muted/50 border-b", children: /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center gap-2", children: [
      /* @__PURE__ */ jsx(ShoppingBag, { className: "w-5 h-5 text-primary" }),
      t("order_details", "Order Details")
    ] }) }),
    /* @__PURE__ */ jsx(CardContent, { className: "p-6", children: /* @__PURE__ */ jsxs("dl", { className: "grid gap-4 text-sm", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center", children: [
        /* @__PURE__ */ jsxs("dt", { className: "flex items-center gap-2 font-medium w-1/2", children: [
          /* @__PURE__ */ jsx(Calendar, { className: "w-4 h-4 text-muted-foreground" }),
          t("order_date", "Order Date")
        ] }),
        /* @__PURE__ */ jsx("dd", { className: "w-1/2", children: formatDate(order.created_at) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center", children: [
        /* @__PURE__ */ jsxs("dt", { className: "flex items-center gap-2 font-medium w-1/2", children: [
          /* @__PURE__ */ jsx(CreditCard, { className: "w-4 h-4 text-muted-foreground" }),
          t("payment_method", "Payment Method")
        ] }),
        /* @__PURE__ */ jsx("dd", { className: "w-1/2", children: t(
          `payment_method_${order.payment_method}`,
          order.payment_method === "cash_on_delivery" ? "Cash on Delivery" : order.payment_method
        ) })
      ] }),
      order.coupon_code && /* @__PURE__ */ jsxs("div", { className: "flex items-center", children: [
        /* @__PURE__ */ jsxs("dt", { className: "flex items-center gap-2 font-medium w-1/2", children: [
          /* @__PURE__ */ jsx(Tag, { className: "w-4 h-4 text-muted-foreground" }),
          t("coupon", "Coupon")
        ] }),
        /* @__PURE__ */ jsx("dd", { className: "w-1/2", children: /* @__PURE__ */ jsx("div", { className: "inline-flex px-2 py-1 text-sm bg-secondary text-secondary-foreground rounded-md font-normal", children: order.coupon_code }) })
      ] }),
      order.notes && /* @__PURE__ */ jsxs("div", { className: "col-span-2 pt-3 border-t", children: [
        /* @__PURE__ */ jsx("dt", { className: "font-medium mb-2", children: t("notes", "Order Notes") }),
        /* @__PURE__ */ jsxs("dd", { className: "bg-muted p-3 rounded-md italic text-muted-foreground", children: [
          '"',
          order.notes,
          '"'
        ] })
      ] })
    ] }) })
  ] });
}
function ShippingAddressCard({ address }) {
  var _a;
  const { t, getLocalizedField } = useI18n();
  return /* @__PURE__ */ jsxs(Card, { className: "overflow-hidden border-none shadow-md transition-all hover:shadow-lg", children: [
    /* @__PURE__ */ jsx(CardHeader, { className: "pb-3 bg-muted/50 border-b", children: /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center gap-2", children: [
      /* @__PURE__ */ jsx(Home$2, { className: "w-5 h-5 text-primary" }),
      t("shipping_address", "Shipping Address")
    ] }) }),
    /* @__PURE__ */ jsx(CardContent, { className: "p-6", children: address ? /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-2", children: [
        /* @__PURE__ */ jsxs(
          Badge,
          {
            variant: "outline",
            className: "bg-primary/5 border-primary/20 text-primary",
            children: [
              /* @__PURE__ */ jsx(Building, { className: "w-3 h-3 mr-1" }),
              getLocalizedField((_a = address.area) == null ? void 0 : _a.gov, "name")
            ]
          }
        ),
        /* @__PURE__ */ jsxs(
          Badge,
          {
            variant: "outline",
            className: "bg-primary/5 border-primary/20 text-primary",
            children: [
              /* @__PURE__ */ jsx(MapPin, { className: "w-3 h-3 mr-1" }),
              getLocalizedField(address.area, "name")
            ]
          }
        )
      ] }),
      /* @__PURE__ */ jsxs(
        "div",
        {
          className: cn(
            "relative bg-gradient-to-r from-muted/80 to-muted p-4 rounded-lg border border-border/50"
          ),
          children: [
            /* @__PURE__ */ jsx("p", { className: "whitespace-pre-line", children: address.content }),
            address.user && /* @__PURE__ */ jsxs("div", { className: "flex items-center mt-3 pt-3 border-t border-border/30", children: [
              /* @__PURE__ */ jsx(User, { className: "w-4 h-4 text-muted-foreground mr-2" }),
              /* @__PURE__ */ jsxs("span", { className: "text-sm text-muted-foreground", children: [
                t("recipient", "Recipient"),
                ":",
                " ",
                address.user.name
              ] })
            ] })
          ]
        }
      )
    ] }) : /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center justify-center h-[160px] bg-muted/30 rounded-lg border border-dashed border-muted-foreground/20", children: [
      /* @__PURE__ */ jsx("div", { className: "bg-muted/50 p-3 rounded-full mb-3", children: /* @__PURE__ */ jsx(AlertCircle, { className: "h-6 w-6 text-muted-foreground" }) }),
      /* @__PURE__ */ jsx("p", { className: "text-sm font-medium text-muted-foreground", children: t(
        "address_not_available",
        "Address information not available"
      ) }),
      /* @__PURE__ */ jsx("p", { className: "text-xs text-muted-foreground/70 max-w-[200px] text-center mt-1", children: t(
        "address_not_found",
        "The shipping address for this order could not be found"
      ) })
    ] }) })
  ] });
}
const Avatar = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  AvatarPrimitive.Root,
  {
    ref,
    className: cn(
      "relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full",
      className
    ),
    ...props
  }
));
Avatar.displayName = AvatarPrimitive.Root.displayName;
const AvatarImage = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  AvatarPrimitive.Image,
  {
    ref,
    className: cn("aspect-square h-full w-full", className),
    ...props
  }
));
AvatarImage.displayName = AvatarPrimitive.Image.displayName;
const AvatarFallback = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  AvatarPrimitive.Fallback,
  {
    ref,
    className: cn(
      "flex h-full w-full items-center justify-center rounded-full bg-muted",
      className
    ),
    ...props
  }
));
AvatarFallback.displayName = AvatarPrimitive.Fallback.displayName;
function OrderItemsList({ items: items2 }) {
  const { t, getLocalizedField } = useI18n();
  console.log("OrderItemsList items:", items2);
  return /* @__PURE__ */ jsxs(Card, { className: "overflow-hidden border-none rounded-b-none", children: [
    /* @__PURE__ */ jsx(CardHeader, { className: "pb-3 bg-muted/50 border-b", children: /* @__PURE__ */ jsxs(CardTitle, { className: "flex items-center gap-2", children: [
      /* @__PURE__ */ jsx(Package, { className: "w-5 h-5 text-primary" }),
      t("order_items", "Order Items")
    ] }) }),
    /* @__PURE__ */ jsx(CardContent, { className: "p-0", children: items2 && items2.length > 0 ? /* @__PURE__ */ jsx("ul", { className: "divide-y divide-border", children: items2.map((item) => /* @__PURE__ */ jsxs(
      "li",
      {
        className: "flex gap-6 py-5 px-6 hover:bg-muted/30 transition-colors",
        children: [
          /* @__PURE__ */ jsxs("div", { className: "h-24 w-16 flex-shrink-0 overflow-hidden rounded-lg border relative group", children: [
            item.variant.images[0] ? /* @__PURE__ */ jsxs(Avatar, { className: "h-full w-full rounded-lg", children: [
              /* @__PURE__ */ jsx(
                AvatarImage,
                {
                  src: item.variant.images[0],
                  alt: getLocalizedField(
                    item.product,
                    "name"
                  ),
                  className: "h-full w-full object-cover object-center transition-transform group-hover:scale-110"
                }
              ),
              /* @__PURE__ */ jsx(AvatarFallback, { className: "rounded-lg", children: /* @__PURE__ */ jsx(Package, { className: "h-8 w-8 text-muted-foreground" }) })
            ] }) : /* @__PURE__ */ jsx("div", { className: "flex h-full w-full items-center justify-center bg-muted rounded-lg", children: /* @__PURE__ */ jsx(Package, { className: "h-8 w-8 text-muted-foreground" }) }),
            /* @__PURE__ */ jsx("div", { className: "absolute -top-2 -right-2 w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-xs font-medium", children: item.quantity })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "flex flex-1 flex-col justify-center", children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("h3", { className: "font-medium text-lg", children: item.product ? getLocalizedField(
                item.product,
                "name"
              ) : t(
                "product_not_available",
                "Product not available"
              ) }),
              item.variant && /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-1.5 mt-1.5", children: [
                item.variant.color && /* @__PURE__ */ jsxs(Badge, { variant: "outline", className: "px-2 py-0 h-5 text-xs", children: [
                  t("color", "Color"),
                  ": ",
                  item.variant.color
                ] }),
                item.variant.size && /* @__PURE__ */ jsxs(Badge, { variant: "outline", className: "px-2 py-0 h-5 text-xs", children: [
                  t("size", "Size"),
                  ": ",
                  item.variant.size
                ] }),
                item.variant.capacity && /* @__PURE__ */ jsxs(Badge, { variant: "outline", className: "px-2 py-0 h-5 text-xs", children: [
                  t("capacity", "Capacity"),
                  ": ",
                  item.variant.capacity
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "mt-1.5 flex items-center text-sm text-muted-foreground", children: [
                /* @__PURE__ */ jsxs("span", { children: [
                  "EGP",
                  Number(
                    item.unit_price
                  ).toFixed(2),
                  " ",
                  t(
                    "per_unit",
                    "per unit"
                  )
                ] }),
                /* @__PURE__ */ jsx("span", { className: "mx-2", children: "•" }),
                /* @__PURE__ */ jsxs("span", { children: [
                  t(
                    "quantity",
                    "Quantity"
                  ),
                  ": ",
                  item.quantity
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("p", { className: "text-right font-medium text-lg", children: [
              "EGP",
              (item.unit_price * item.quantity).toFixed(2)
            ] })
          ] }) })
        ]
      },
      item.id
    )) }) : /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center justify-center py-12", children: [
      /* @__PURE__ */ jsx(Package, { className: "h-12 w-12 text-muted-foreground mb-4" }),
      /* @__PURE__ */ jsx("h3", { className: "text-lg font-medium", children: t("no_items", "No items in this order") }),
      /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground mt-1", children: t(
        "empty_order",
        "This order doesn't contain any items"
      ) })
    ] }) })
  ] });
}
const Separator = React.forwardRef(
  ({ className, orientation = "horizontal", decorative = true, ...props }, ref) => /* @__PURE__ */ jsx(
    SeparatorPrimitive.Root,
    {
      ref,
      decorative,
      orientation,
      className: cn(
        "shrink-0 bg-border",
        orientation === "horizontal" ? "h-[1px] w-full" : "h-full w-[1px]",
        className
      ),
      ...props
    }
  )
);
Separator.displayName = SeparatorPrimitive.Root.displayName;
function OrderSummary({ order }) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsx(CardFooter, { className: "pt-6 bg-muted/20", children: /* @__PURE__ */ jsxs("dl", { className: "space-y-4 text-sm w-full", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
      /* @__PURE__ */ jsx("span", { className: "text-muted-foreground", children: t("subtotal", "Subtotal") }),
      /* @__PURE__ */ jsxs("span", { children: [
        "EGP ",
        Number(order.subtotal).toFixed(2)
      ] })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
      /* @__PURE__ */ jsx("span", { className: "text-muted-foreground", children: t("shipping", "Shipping") }),
      /* @__PURE__ */ jsxs("span", { children: [
        "EGP ",
        Number(order.shipping_cost).toFixed(2)
      ] })
    ] }),
    Number(order.discount) > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center text-green-600", children: [
      /* @__PURE__ */ jsx("dt", { children: t("discount", "Discount") }),
      /* @__PURE__ */ jsx("dd", { className: "font-medium", children: /* @__PURE__ */ jsxs("span", { className: "text-destructive", children: [
        "-EGP ",
        Number(order.discount).toFixed(2)
      ] }) })
    ] }),
    /* @__PURE__ */ jsx(Separator, { className: "my-2" }),
    /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center font-medium text-xl", children: [
      /* @__PURE__ */ jsx("dt", { children: t("total", "Total") }),
      /* @__PURE__ */ jsxs("dd", { className: "text-primary", children: [
        "EGP ",
        Number(order.total).toFixed(2)
      ] })
    ] })
  ] }) });
}
function OrderItemsCard({ order }) {
  return /* @__PURE__ */ jsxs(Card, { className: "overflow-hidden border-none shadow-md", children: [
    order.items && /* @__PURE__ */ jsx(OrderItemsList, { items: order.items }),
    /* @__PURE__ */ jsx(OrderSummary, { order })
  ] });
}
function CancellationPolicy({}) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs(Alert, { className: "border-none shadow-md bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300", children: [
    /* @__PURE__ */ jsx(AlertCircle, { className: "h-5 w-5" }),
    /* @__PURE__ */ jsx(AlertTitle, { className: "ml-2 font-semibold", children: t(
      "cancellation_policy_title",
      "Cancellation Policy"
    ) }),
    /* @__PURE__ */ jsx(AlertDescription, { className: "ml-2", children: t(
      "cancellation_policy_desc",
      "You can cancel your order while it's still in the processing stage. Once the order status changes to 'Shipped', it can no longer be cancelled."
    ) })
  ] });
}
function OrderThankYou({}) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs("div", { className: "text-center py-6 mt-2", children: [
    /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-primary mb-1", children: t("thank_you", "Thank You for Your Order!") }),
    /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t(
      "order_support",
      "If you have any questions about your order, please contact our support team."
    ) })
  ] });
}
function ReturnOrderModal({ orderId, canRequestReturn }) {
  const [open, setOpen] = useState(false);
  const { t } = useI18n();
  const { data, setData, post, processing, errors, reset } = useForm({
    reason: ""
  });
  const handleSubmit = (e) => {
    e.preventDefault();
    post(route("orders.return.request", orderId), {
      onSuccess: () => {
        setOpen(false);
        reset();
      }
    });
  };
  if (!canRequestReturn) {
    return null;
  }
  return /* @__PURE__ */ jsxs(Dialog, { open, onOpenChange: setOpen, children: [
    "      ",
    /* @__PURE__ */ jsx(DialogTrigger, { asChild: true, children: /* @__PURE__ */ jsxs(Button, { variant: "outline", className: "flex items-center gap-2", children: [
      /* @__PURE__ */ jsx(RotateCcw, { className: "h-4 w-4" }),
      t("request_return", "Request Return")
    ] }) }),
    /* @__PURE__ */ jsxs(DialogContent, { className: "sm:max-w-[425px]", children: [
      /* @__PURE__ */ jsxs(DialogHeader, { children: [
        /* @__PURE__ */ jsx(DialogTitle, { children: t("request_order_return", "Request Order Return") }),
        /* @__PURE__ */ jsx(DialogDescription, { children: t("return_request_description", "Please specify the reason for the return request. Your request will be reviewed by our customer service team.") })
      ] }),
      /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit, className: "space-y-4", children: [
        /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
          /* @__PURE__ */ jsx(Label, { htmlFor: "reason", children: t("return_reason", "Return Reason") }),
          /* @__PURE__ */ jsx(
            Textarea,
            {
              id: "reason",
              value: data.reason,
              onChange: (e) => setData("reason", e.target.value),
              placeholder: t("return_reason_placeholder", "Write the reason for the return request here..."),
              rows: 4,
              className: "resize-none"
            }
          ),
          errors.reason && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.reason })
        ] }),
        /* @__PURE__ */ jsxs(Alert, { children: [
          /* @__PURE__ */ jsx(AlertCircle, { className: "h-4 w-4" }),
          /* @__PURE__ */ jsx(AlertDescription, { children: t("return_policy_notice", "Returns can be requested within 14 days of delivery. Your request will be reviewed within 24-48 hours.") })
        ] }),
        /* @__PURE__ */ jsxs(DialogFooter, { className: "flex gap-2", children: [
          /* @__PURE__ */ jsx(
            Button,
            {
              type: "button",
              variant: "outline",
              onClick: () => setOpen(false),
              disabled: processing,
              children: t("cancel", "Cancel")
            }
          ),
          /* @__PURE__ */ jsx(Button, { type: "submit", disabled: processing || !data.reason.trim(), children: processing ? t("sending", "Sending...") : t("submit_return_request", "Submit Return Request") })
        ] })
      ] })
    ] })
  ] });
}
function Show$1({ order, canRequestReturn = false }) {
  const { t } = useI18n();
  const getPaymentStatusBadgeColor = (status) => {
    switch (status) {
      case "paid":
        return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
      case "pending":
        return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
      default:
        return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
    }
  };
  const getReturnStatusBadgeColor = (status) => {
    switch (status) {
      case "return_requested":
        return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
      case "return_approved":
        return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500";
      case "return_rejected":
        return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500";
      case "item_returned":
      case "refund_processed":
        return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
      default:
        return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
    }
  };
  const needsPayment = () => {
    return order.payment_method !== "cash_on_delivery" && order.payment_status === "pending" && order.order_status !== "cancelled";
  };
  const handleCancelOrder = () => {
    if (confirm(
      t(
        "confirm_cancel_order",
        "Are you sure you want to cancel this order?"
      )
    )) {
      router.patch(
        route("orders.cancel", order.id),
        {},
        {
          onSuccess: () => {
          }
        }
      );
    }
  };
  console.log(canRequestReturn);
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("order_details", "Order Details") }),
    /* @__PURE__ */ jsx("div", { className: "container mt-4", children: /* @__PURE__ */ jsxs("div", { className: "space-y-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-4", children: [
        /* @__PURE__ */ jsx(
          PageTitle,
          {
            title: `${t("order_number", "Order")} #${order.id}`,
            backUrl: route("orders.index"),
            backText: t("back_to_orders", "Back to Orders"),
            className: "pb-4 border-b"
          }
        ),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap items-center justify-between gap-4", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-4 items-center", children: [
            /* @__PURE__ */ jsxs("div", { className: "flex gap-2 items-center", children: [
              /* @__PURE__ */ jsxs("span", { className: "text-sm font-medium", children: [
                t("payment_status", "Payment Status"),
                ":"
              ] }),
              /* @__PURE__ */ jsx(
                Badge,
                {
                  variant: "outline",
                  className: getPaymentStatusBadgeColor(order.payment_status),
                  children: t(`payment_status_${order.payment_status}`, order.payment_status)
                }
              )
            ] }),
            order.return_status && /* @__PURE__ */ jsxs("div", { className: "flex gap-2 items-center", children: [
              /* @__PURE__ */ jsxs("span", { className: "text-sm font-medium", children: [
                t("return_status", "Return Status"),
                ":"
              ] }),
              /* @__PURE__ */ jsx(
                Badge,
                {
                  variant: "outline",
                  className: getReturnStatusBadgeColor(order.return_status),
                  children: t(`return_status_${order.return_status}`, order.return_status)
                }
              )
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-2", children: [
            needsPayment() && /* @__PURE__ */ jsx(
              Button,
              {
                asChild: true,
                className: "flex items-center gap-1",
                children: /* @__PURE__ */ jsxs("a", { href: route("kashier.payment.show", order.id), children: [
                  /* @__PURE__ */ jsx(CreditCard, { className: "h-4 w-4" }),
                  t("complete_your_payment", "Complete Your Payment")
                ] })
              }
            ),
            canRequestReturn && !order.return_status && /* @__PURE__ */ jsx(
              ReturnOrderModal,
              {
                orderId: order.id,
                canRequestReturn
              }
            ),
            /* @__PURE__ */ jsx(Button, { asChild: true, variant: "outline", children: /* @__PURE__ */ jsxs("a", { href: route("orders.returns.history"), children: [
              /* @__PURE__ */ jsx(RotateCcw, { className: "h-4 w-4" }),
              t("return_history", "Return History")
            ] }) })
          ] })
        ] })
      ] }),
      order.order_status !== "cancelled" && /* @__PURE__ */ jsx(OrderProgress, { order }),
      order.order_status === "cancelled" && /* @__PURE__ */ jsx(OrderCancelledAlert, {}),
      order.return_status && order.return_reason && /* @__PURE__ */ jsx("div", { className: "bg-orange-50 border border-orange-200 rounded-lg p-4 dark:bg-orange-900/20 dark:border-orange-800", children: /* @__PURE__ */ jsxs("div", { className: "flex items-start gap-3", children: [
        /* @__PURE__ */ jsx(RotateCcw, { className: "h-5 w-5 text-orange-600 dark:text-orange-400 mt-0.5" }),
        /* @__PURE__ */ jsxs("div", { className: "flex-1", children: [
          /* @__PURE__ */ jsx("h3", { className: "font-medium text-orange-800 dark:text-orange-200 mb-2", children: t("return_request_information", "Return Request Information") }),
          /* @__PURE__ */ jsxs("div", { className: "space-y-2 text-sm", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("span", { className: "font-medium text-orange-700 dark:text-orange-300", children: [
                t("return_status", "Status"),
                ":"
              ] }),
              /* @__PURE__ */ jsx("span", { className: "ml-2 text-orange-600 dark:text-orange-400", children: t(`return_status_${order.return_status}`, order.return_status) })
            ] }),
            order.return_requested_at && /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("span", { className: "font-medium text-orange-700 dark:text-orange-300", children: [
                t("requested_on", "Requested on"),
                ":"
              ] }),
              /* @__PURE__ */ jsx("span", { className: "ml-2 text-orange-600 dark:text-orange-400", children: new Date(order.return_requested_at).toLocaleDateString() })
            ] }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("span", { className: "font-medium text-orange-700 dark:text-orange-300", children: [
                t("return_reason", "Reason"),
                ":"
              ] }),
              /* @__PURE__ */ jsx("p", { className: "mt-1 text-orange-600 dark:text-orange-400", children: order.return_reason })
            ] })
          ] })
        ] })
      ] }) }),
      /* @__PURE__ */ jsxs("div", { className: "grid gap-6 md:grid-cols-2", children: [
        /* @__PURE__ */ jsx(OrderDetailsCard, { order }),
        /* @__PURE__ */ jsx(ShippingAddressCard, { address: order.shipping_address })
      ] }),
      /* @__PURE__ */ jsx(OrderItemsCard, { order }),
      order.order_status === "processing" && /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsx(CancellationPolicy, {}),
        /* @__PURE__ */ jsx("div", { className: "flex justify-center", children: /* @__PURE__ */ jsx(
          Button,
          {
            variant: "destructive",
            onClick: handleCancelOrder,
            className: "mt-2",
            children: t("cancel_order", "Cancel Order")
          }
        ) })
      ] }),
      /* @__PURE__ */ jsx(OrderThankYou, {})
    ] }) })
  ] });
}
const __vite_glob_0_13 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show$1
}, Symbol.toStringTag, { value: "Module" }));
function PolicyPage({ content, title }) {
  const { currentLocale, direction } = useI18n();
  const localizedContent = content[currentLocale] || content.en || "";
  const localizedTitle = title[currentLocale] || title.en || "";
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: localizedTitle }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-slate-700/25 dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]" }),
      /* @__PURE__ */ jsx("div", { className: "relative", children: /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 py-16 sm:py-24", children: /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("div", { className: "mx-auto mb-6 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 p-0.5", children: /* @__PURE__ */ jsx("div", { className: "flex h-full w-full items-center justify-center rounded-full bg-white dark:bg-slate-900", children: /* @__PURE__ */ jsx("svg", { className: "h-8 w-8 text-blue-500", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" }) }) }) }),
        /* @__PURE__ */ jsx("h1", { className: "text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl", children: /* @__PURE__ */ jsx("span", { className: "bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600 bg-clip-text text-transparent", children: localizedTitle }) }),
        /* @__PURE__ */ jsx("p", { className: "mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300", children: currentLocale === "ar" ? "نحن ملتزمون بالشفافية وحماية حقوقك. اقرأ سياساتنا بعناية." : "We are committed to transparency and protecting your rights. Please read our policies carefully." })
      ] }) }) })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "bg-white dark:bg-slate-900", children: /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 py-16", children: /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-4xl", children: [
      /* @__PURE__ */ jsxs(Card, { className: "border-0 bg-white/70 shadow-2xl backdrop-blur-sm dark:bg-slate-800/70 dark:shadow-slate-900/50", children: [
        /* @__PURE__ */ jsx("div", { className: "absolute inset-0 rounded-lg bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-blue-500/5" }),
        /* @__PURE__ */ jsxs(CardContent, { className: "relative p-8 sm:p-12", children: [
          /* @__PURE__ */ jsx("div", { className: "absolute -top-6 left-8 h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 opacity-20" }),
          /* @__PURE__ */ jsx("div", { className: "absolute -bottom-6 right-8 h-8 w-8 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 opacity-20" }),
          /* @__PURE__ */ jsx(
            "div",
            {
              className: `prose prose-lg max-w-none text-slate-700 dark:prose-invert dark:text-slate-300 ${direction === "rtl" ? "prose-rtl" : ""}`,
              style: {
                lineHeight: "1.8",
                fontSize: "1.1rem"
              },
              children: /* @__PURE__ */ jsx("div", { className: "space-y-6", dangerouslySetInnerHTML: { __html: localizedContent } })
            }
          ),
          /* @__PURE__ */ jsx("div", { className: "mt-12 flex justify-center", children: /* @__PURE__ */ jsx("div", { className: "h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 via-purple-500 to-blue-500" }) })
        ] })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "mt-8 text-center", children: /* @__PURE__ */ jsx("p", { className: "text-sm text-slate-500 dark:text-slate-400", children: currentLocale === "ar" ? "آخر تحديث: " + (/* @__PURE__ */ new Date()).toLocaleDateString("ar-EG") : "Last updated: " + (/* @__PURE__ */ new Date()).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric"
      }) }) })
    ] }) }) })
  ] });
}
function Contact({ content, title }) {
  return /* @__PURE__ */ jsx(PolicyPage, { content, title });
}
const __vite_glob_0_14 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Contact
}, Symbol.toStringTag, { value: "Module" }));
function FacebookDataDeletion() {
  const settings = useSettings();
  return /* @__PURE__ */ jsxs("div", { dir: "ltr", children: [
    /* @__PURE__ */ jsx(Head, { title: "Facebook Deletion" }),
    /* @__PURE__ */ jsxs("div", { className: "relative bg-gradient-to-br from-blue-50 via-white to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-slate-700/25 dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]" }),
      /* @__PURE__ */ jsx("div", { className: "relative", children: /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 py-16 sm:py-24", children: /* @__PURE__ */ jsxs("div", { className: "text-center", children: [
        /* @__PURE__ */ jsx("div", { className: "mx-auto mb-6 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 p-0.5", children: /* @__PURE__ */ jsx("div", { className: "flex h-full w-full items-center justify-center rounded-full bg-white dark:bg-slate-900", children: /* @__PURE__ */ jsx(Facebook, { className: "h-8 w-8 text-blue-500" }) }) }),
        /* @__PURE__ */ jsx("h1", { className: "text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl", children: /* @__PURE__ */ jsx("span", { className: "bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-600 bg-clip-text text-transparent", children: "Facebook Deletion" }) }),
        /* @__PURE__ */ jsx("p", { className: "mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300", children: "Protect your privacy and control your data. Follow these steps to delete your data from Facebook." })
      ] }) }) })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "bg-white dark:bg-slate-900", children: /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 py-16", children: /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-4xl space-y-8", children: [
      /* @__PURE__ */ jsx(Card, { className: "border-0 bg-white/70 shadow-2xl backdrop-blur-sm dark:bg-slate-800/70 dark:shadow-slate-900/50", children: /* @__PURE__ */ jsxs(CardContent, { className: "relative p-8 sm:p-12", children: [
        /* @__PURE__ */ jsxs("div", { className: "mt-12 pt-8", children: [
          /* @__PURE__ */ jsxs("h3", { className: "text-xl font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2", children: [
            /* @__PURE__ */ jsx(Mail, { className: "h-5 w-5 text-blue-500" }),
            "By Email"
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "bg-slate-50 dark:bg-slate-800/50 rounded-lg p-6", children: [
            /* @__PURE__ */ jsx("p", { className: "text-slate-700 dark:text-slate-300 mb-4 leading-relaxed", children: "Users have the right to request the deletion of their personal data collected by our platform. If you would like to request deletion of your data, you can do so by contacting us through one of the following methods:" }),
            /* @__PURE__ */ jsx("div", { className: "space-y-3", children: /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("strong", { className: "text-slate-900 dark:text-white", children: [
                "Email: ",
                settings.contact_email || ""
              ] }),
              /* @__PURE__ */ jsx("p", { className: "text-slate-600 dark:text-slate-400 mt-1", children: 'Send a request to our email with the subject line: "Data Deletion Request". Please include your full name, registered email address, and a brief description of your request.' })
            ] }) }),
            /* @__PURE__ */ jsx("div", { className: "mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800", children: /* @__PURE__ */ jsx("p", { className: "text-amber-800 dark:text-amber-200 text-sm leading-relaxed", children: "We will respond to all verified requests within 30 days in accordance with applicable data protection regulations. Some data may be retained if required by law or for legitimate business purposes, such as fraud prevention or tax compliance." }) })
          ] })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "mt-8 text-center", children: /* @__PURE__ */ jsx(
          Button,
          {
            className: "bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-8 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105",
            asChild: true,
            children: /* @__PURE__ */ jsxs(
              "a",
              {
                href: `mailto:${settings.contact_email || "info@vilain.com"}?subject=Data Deletion Request`,
                children: [
                  /* @__PURE__ */ jsx(Mail, { className: "h-4 w-4 mr-2" }),
                  "Contact Us",
                  /* @__PURE__ */ jsx(ExternalLink, { className: "h-4 w-4 ml-2" })
                ]
              }
            )
          }
        ) }),
        /* @__PURE__ */ jsx("div", { className: "mt-12 flex justify-center", children: /* @__PURE__ */ jsx("div", { className: "h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-500" }) })
      ] }) }),
      /* @__PURE__ */ jsx("div", { className: "text-center", children: /* @__PURE__ */ jsx("p", { className: "text-sm text-slate-500 dark:text-slate-400", children: "Last updated: " + (/* @__PURE__ */ new Date()).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric"
      }) }) })
    ] }) }) })
  ] });
}
const __vite_glob_0_15 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: FacebookDataDeletion
}, Symbol.toStringTag, { value: "Module" }));
function Privacy({ content, title }) {
  return /* @__PURE__ */ jsx(PolicyPage, { content, title });
}
const __vite_glob_0_16 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Privacy
}, Symbol.toStringTag, { value: "Module" }));
function Returns({ content, title }) {
  return /* @__PURE__ */ jsx(PolicyPage, { content, title });
}
const __vite_glob_0_17 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Returns
}, Symbol.toStringTag, { value: "Module" }));
function Terms({ content, title }) {
  return /* @__PURE__ */ jsx(PolicyPage, { content, title });
}
const __vite_glob_0_18 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Terms
}, Symbol.toStringTag, { value: "Module" }));
function Kashier({ kashierParams }) {
  const { t } = useI18n();
  const iframeLoaded = useRef(false);
  const iframeErrored = useRef(false);
  console.log("Kashier Params:", kashierParams);
  useEffect(() => {
    const script = document.createElement("script");
    script.id = "kashier-iFrame";
    script.src = "https://payments.kashier.io/kashier-checkout.js";
    script.setAttribute("data-amount", kashierParams.amount);
    script.setAttribute("data-hash", kashierParams.hash);
    script.setAttribute("data-currency", kashierParams.currency);
    script.setAttribute("data-orderid", kashierParams.orderId);
    script.setAttribute("data-merchantid", kashierParams.merchantId);
    script.setAttribute("data-merchantredirect", kashierParams.merchantRedirect);
    script.setAttribute("data-serverwebhook", kashierParams.serverWebhook);
    script.setAttribute("data-failureredirect", kashierParams.failureRedirect);
    script.setAttribute("data-mode", kashierParams.mode);
    script.setAttribute("data-display", kashierParams.displayMode);
    script.setAttribute("data-allowedmethods", kashierParams.allowedMethods);
    script.setAttribute("data-paymentrequestId", kashierParams.paymentRequestId);
    script.onload = () => {
      console.log("Kashier script loaded successfully");
      iframeLoaded.current = true;
    };
    script.onerror = () => {
      console.error("Failed to load Kashier script");
      iframeErrored.current = true;
    };
    document.getElementById("kashier-iFrame-container").appendChild(script);
    return () => {
      if (script.parentNode) {
        script.parentNode.removeChild(script);
      }
    };
  }, [kashierParams]);
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("processing_payment", "Processing Payment") }),
    /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
      /* @__PURE__ */ jsx(
        PageTitle,
        {
          title: t("processing_payment", "Processing Payment"),
          backUrl: route("checkout.index"),
          backText: t("back_to_order", "Back to Order")
        }
      ),
      /* @__PURE__ */ jsxs("div", { className: "grid gap-6", children: [
        /* @__PURE__ */ jsx(Card, { className: "p-6 flex flex-col items-center justify-center min-h-[400px]", children: /* @__PURE__ */ jsxs("div", { className: "text-center space-y-6", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center gap-4", children: [
            /* @__PURE__ */ jsx("h2", { className: "text-xl font-medium", children: t("initializing_payment", "Initializing Payment") }),
            /* @__PURE__ */ jsx("p", { className: "text-muted-foreground max-w-md", children: t("payment_message", "Please wait while we connect to the secure payment gateway. Do not close this page.") })
          ] }),
          /* @__PURE__ */ jsx("div", { className: "p-4 bg-muted rounded-lg mt-8", children: /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-3 text-sm", children: [
            /* @__PURE__ */ jsxs("div", { className: "text-muted-foreground", children: [
              t("order_number", "Order Number"),
              ":"
            ] }),
            /* @__PURE__ */ jsx("div", { className: "font-medium text-end", children: kashierParams.orderId }),
            /* @__PURE__ */ jsxs("div", { className: "text-muted-foreground", children: [
              t("amount", "Amount"),
              ":"
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "font-medium text-end", children: [
              kashierParams.amount,
              " ",
              kashierParams.currency
            ] })
          ] }) }),
          /* @__PURE__ */ jsx("div", { id: "kashier-iFrame-container" })
        ] }) }),
        /* @__PURE__ */ jsxs(Alert, { children: [
          /* @__PURE__ */ jsx(AlertCircle, { className: "h-4 w-4" }),
          /* @__PURE__ */ jsx(AlertTitle, { children: t("payment_help_title", "Having trouble with payment?") }),
          /* @__PURE__ */ jsx(AlertDescription, { children: t("payment_help_message", "If the payment window does not appear, please try refreshing this page. For assistance, contact our support team.") })
        ] })
      ] })
    ] })
  ] });
}
const __vite_glob_0_19 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Kashier
}, Symbol.toStringTag, { value: "Module" }));
function SectionPage({
  section
}) {
  const { getLocalizedField, t, direction } = useI18n();
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: getLocalizedField(section, "title") }),
    /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
      /* @__PURE__ */ jsx(
        PageTitle,
        {
          title: getLocalizedField(section, "title"),
          icon: /* @__PURE__ */ jsx(Layers, { className: "h-6 w-6" })
        }
      ),
      /* @__PURE__ */ jsx(
        ProductGrid,
        {
          sectionId: section.id,
          className: "!pt-0",
          viewType: "grid",
          emptyMessage: t(
            "no_products_available",
            "No products available in this section"
          )
        },
        section.id
      )
    ] })
  ] });
}
const __vite_glob_0_20 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: SectionPage
}, Symbol.toStringTag, { value: "Module" }));
function ProductActions({
  product,
  quantity: quantity2,
  selectedVariant
}) {
  const { t } = useI18n();
  const { addToCart, addingToCart } = useCart();
  const handleAddToCart = () => {
    if (selectedVariant) {
      if (selectedVariant.quantity > 0) {
        addToCart(product.id, quantity2, selectedVariant.id);
      }
    } else if (product.total_quantity && product.total_quantity > 0) {
      addToCart(product.id, quantity2);
    }
  };
  const isOutOfStock = selectedVariant ? selectedVariant.quantity <= 0 : Boolean(product.total_quantity && product.total_quantity <= 0);
  const isInWishlist = product.isInWishlist;
  const addToWishlist = () => {
    router.post(
      route("wishlist.add"),
      { product_id: product.id },
      {
        preserveScroll: true
      }
    );
  };
  const removeFromWishlist = () => {
    router.delete(
      route("wishlist.remove", product.id),
      {
        preserveScroll: true
      }
    );
  };
  return /* @__PURE__ */ jsxs("div", { className: "flex gap-3 flex-row", children: [
    /* @__PURE__ */ jsxs(
      Button,
      {
        onClick: handleAddToCart,
        disabled: addingToCart[product.id] || isOutOfStock,
        className: "flex-1",
        size: "lg",
        children: [
          /* @__PURE__ */ jsx(ShoppingBag, { className: "w-5 h-5 mr-2" }),
          addingToCart[product.id] ? t("adding_to_cart", "Adding to Cart...") : isOutOfStock ? t("out_of_stock", "Out of Stock") : t("add_to_cart", "Add to Cart")
        ]
      }
    ),
    /* @__PURE__ */ jsxs(
      Button,
      {
        variant: "outline",
        size: "lg",
        className: "flex-shrink-0",
        onClick: isInWishlist ? removeFromWishlist : addToWishlist,
        children: [
          /* @__PURE__ */ jsx(
            Heart,
            {
              className: "w-5 h-5",
              fill: isInWishlist ? "red" : "none",
              stroke: isInWishlist ? "red" : "currentColor"
            }
          ),
          /* @__PURE__ */ jsx("span", { className: "sr-only", children: t("add_to_wishlist", "Add to Wishlist") })
        ]
      }
    )
  ] });
}
const ScrollArea = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  ScrollAreaPrimitive.Root,
  {
    ref,
    className: cn("relative overflow-hidden", className),
    ...props,
    children: [
      /* @__PURE__ */ jsx(ScrollAreaPrimitive.Viewport, { className: "h-full w-full rounded-[inherit]", children }),
      /* @__PURE__ */ jsx(ScrollBar, {}),
      /* @__PURE__ */ jsx(ScrollAreaPrimitive.Corner, {})
    ]
  }
));
ScrollArea.displayName = ScrollAreaPrimitive.Root.displayName;
const ScrollBar = React.forwardRef(({ className, orientation = "vertical", ...props }, ref) => /* @__PURE__ */ jsx(
  ScrollAreaPrimitive.ScrollAreaScrollbar,
  {
    ref,
    orientation,
    className: cn(
      "flex touch-none select-none transition-colors",
      orientation === "vertical" && "h-full w-2.5 border-l border-l-transparent p-[1px]",
      orientation === "horizontal" && "h-2.5 flex-col border-t border-t-transparent p-[1px]",
      className
    ),
    ...props,
    children: /* @__PURE__ */ jsx(ScrollAreaPrimitive.ScrollAreaThumb, { className: "relative flex-1 rounded-full bg-border" })
  }
));
ScrollBar.displayName = ScrollAreaPrimitive.ScrollAreaScrollbar.displayName;
function ProductDescription({
  product
}) {
  const { getLocalizedField, t, direction } = useI18n();
  const description2 = getLocalizedField(product, "description");
  return /* @__PURE__ */ jsxs("div", { className: "w-full", children: [
    /* @__PURE__ */ jsx("h3", { className: "text-lg font-medium mb-2", children: t("description", "Description") }),
    description2 ? /* @__PURE__ */ jsx(ScrollArea, { className: "h-[200px]", dir: direction, children: /* @__PURE__ */ jsx("div", { className: "text-muted-foreground whitespace-pre-wrap", children: description2 }) }) : /* @__PURE__ */ jsx("div", { className: "text-muted-foreground italic", children: t("no_description", "No description available.") }),
    /* @__PURE__ */ jsx(Separator, { className: "mt-6" })
  ] });
}
function ProductGallery({
  product,
  selectedVariant
}) {
  const { getLocalizedField } = useI18n();
  const images = (selectedVariant == null ? void 0 : selectedVariant.images) || product.all_images || [];
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  if (images.length === 0) {
    return /* @__PURE__ */ jsx("div", { className: "aspect-square relative bg-muted rounded-md flex items-center justify-center", children: /* @__PURE__ */ jsx(
      Image,
      {
        src: "/placeholder.jpg",
        alt: getLocalizedField(product, "name"),
        className: "object-contain w-full h-full"
      }
    ) });
  }
  const prevImage = () => {
    setCurrentImageIndex((prev) => prev === 0 ? images.length - 1 : prev - 1);
  };
  const nextImage = () => {
    setCurrentImageIndex((prev) => prev === images.length - 1 ? 0 : prev + 1);
  };
  const goToImage = (index) => {
    setCurrentImageIndex(index);
  };
  return /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
    /* @__PURE__ */ jsxs("div", { className: "aspect-square relative bg-muted rounded-md overflow-hidden", children: [
      /* @__PURE__ */ jsx(
        Image,
        {
          src: images[currentImageIndex],
          alt: getLocalizedField(product, "name"),
          className: "object-contain w-full h-full"
        }
      ),
      images.length > 1 && /* @__PURE__ */ jsxs(Fragment, { children: [
        /* @__PURE__ */ jsx(
          Button,
          {
            variant: "outline",
            size: "icon",
            className: "absolute left-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full opacity-70 hover:opacity-100",
            onClick: prevImage,
            children: /* @__PURE__ */ jsx(ChevronLeft, { className: "h-4 w-4" })
          }
        ),
        /* @__PURE__ */ jsx(
          Button,
          {
            variant: "outline",
            size: "icon",
            className: "absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full opacity-70 hover:opacity-100",
            onClick: nextImage,
            children: /* @__PURE__ */ jsx(ChevronRight, { className: "h-4 w-4" })
          }
        )
      ] })
    ] }),
    "                ",
    images.length > 1 && /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2 justify-center", children: images.map((image, index) => /* @__PURE__ */ jsx(
      "button",
      {
        className: cn(
          "w-16 h-16 rounded border overflow-hidden",
          index === currentImageIndex ? "border-primary ring-2 ring-primary ring-offset-2" : "border-muted-foreground/20"
        ),
        onClick: () => goToImage(index),
        children: /* @__PURE__ */ jsx(
          Image,
          {
            src: image,
            alt: `${getLocalizedField(product, "name")} - ${index + 1}`,
            className: "object-cover w-full h-full"
          }
        )
      },
      index
    )) })
  ] });
}
function ProductInfo({ product, selectedVariant }) {
  const { getLocalizedField, t } = useI18n();
  const currentPrice = (selectedVariant == null ? void 0 : selectedVariant.price) ?? product.price;
  const currentSalePrice = (selectedVariant == null ? void 0 : selectedVariant.sale_price) ?? product.sale_price;
  const isInStock = selectedVariant ? selectedVariant.quantity > 0 : product.isInStock;
  const hasDiscount = currentSalePrice && currentSalePrice !== currentPrice;
  const discountPercentage = hasDiscount ? Math.round((currentPrice - currentSalePrice) / currentPrice * 100) : 0;
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
    product.brand && /* @__PURE__ */ jsx(
      Link,
      {
        href: `/search?brands[]=${product.brand.id}`,
        className: "w-fit mb-3",
        children: /* @__PURE__ */ jsx(
          Badge,
          {
            variant: "outline",
            className: "hover:bg-primary hover:text-primary-foreground transition-colors cursor-pointer",
            children: getLocalizedField(product.brand, "name")
          }
        )
      }
    ),
    /* @__PURE__ */ jsx("h1", { className: "text-3xl md:text-4xl font-bold mb-4", children: getLocalizedField(product, "name") }),
    /* @__PURE__ */ jsx("div", { className: "flex items-center gap-3 mb-6", children: hasDiscount ? /* @__PURE__ */ jsxs(Fragment, { children: [
      /* @__PURE__ */ jsxs("span", { className: "text-2xl md:text-3xl font-bold", children: [
        currentSalePrice,
        " EGP"
      ] }),
      /* @__PURE__ */ jsxs("span", { className: "text-xl text-muted-foreground line-through", children: [
        currentPrice,
        " EGP"
      ] }),
      discountPercentage > 0 && /* @__PURE__ */ jsxs(Badge, { variant: "destructive", className: "ml-2", children: [
        discountPercentage,
        "% ",
        t("off", "OFF")
      ] })
    ] }) : /* @__PURE__ */ jsxs("span", { className: "text-2xl md:text-3xl font-bold", children: [
      currentPrice,
      " EGP"
    ] }) }),
    /* @__PURE__ */ jsxs("div", { className: "flex justify-between", children: [
      product.category && /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 mb-6", children: [
        /* @__PURE__ */ jsxs("span", { className: "text-muted-foreground", children: [
          t("category", "Category"),
          ":"
        ] }),
        /* @__PURE__ */ jsx(
          Link,
          {
            href: `/search?categories[]=${product.category.id}`,
            className: "text-primary hover:underline",
            children: getLocalizedField(product.category, "name")
          }
        )
      ] }),
      /* @__PURE__ */ jsx("div", { className: "mb-6", children: /* @__PURE__ */ jsxs(
        Badge,
        {
          variant: isInStock ? "outline" : "destructive",
          className: "inline-flex items-center gap-1.5 py-1 px-3",
          children: [
            isInStock ? /* @__PURE__ */ jsx(Check, { className: "h-3.5 w-3.5 text-green-600" }) : /* @__PURE__ */ jsx(AlertCircle, { className: "h-3.5 w-3.5" }),
            /* @__PURE__ */ jsx("span", { className: "font-medium", children: isInStock ? `${t("in_stock", "In Stock")} · ${selectedVariant ? selectedVariant.quantity : product.totalQuantity} ${t("available", "available")}` : t("out_of_stock", "Out of Stock") })
          ]
        }
      ) })
    ] })
  ] });
}
function ProductQuantitySelector({ maxQuantity, onChange }) {
  const { t } = useI18n();
  const [quantity2, setQuantity] = useState(1);
  const handleQuantityChange = (newQuantity) => {
    const validQuantity = Math.max(1, Math.min(newQuantity, maxQuantity));
    setQuantity(validQuantity);
    onChange(validQuantity);
  };
  const handleInputChange = (e) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value)) {
      handleQuantityChange(value);
    }
  };
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-2", children: [
    /* @__PURE__ */ jsx("label", { htmlFor: "quantity", className: "font-medium", children: t("quantity", "Quantity") }),
    /* @__PURE__ */ jsxs("div", { className: "flex items-center", children: [
      /* @__PURE__ */ jsx(
        Button,
        {
          type: "button",
          variant: "outline",
          size: "icon",
          onClick: () => handleQuantityChange(quantity2 - 1),
          disabled: quantity2 <= 1,
          className: "ltr:rounded-r-none rtl:rounded-l-none",
          children: /* @__PURE__ */ jsx(Minus, { className: "h-4 w-4" })
        }
      ),
      /* @__PURE__ */ jsx(
        Input,
        {
          id: "quantity",
          type: "number",
          value: quantity2,
          onChange: handleInputChange,
          min: 1,
          max: maxQuantity,
          className: "w-16 text-center rounded-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
        }
      ),
      /* @__PURE__ */ jsx(
        Button,
        {
          type: "button",
          variant: "outline",
          size: "icon",
          onClick: () => handleQuantityChange(quantity2 + 1),
          disabled: quantity2 >= maxQuantity,
          className: "ltr:rounded-l-none rtl:rounded-r-none",
          children: /* @__PURE__ */ jsx(Plus, { className: "h-4 w-4" })
        }
      )
    ] })
  ] });
}
function ProductVariantSelector({
  product,
  onVariantChange,
  selectedVariantId
}) {
  var _a, _b, _c, _d, _e, _f, _g, _h, _i;
  const { t } = useI18n();
  const [selectedVariant, setSelectedVariant] = useState(null);
  ((_a = product.variants) == null ? void 0 : _a.filter((v) => v.color)) || [];
  ((_b = product.variants) == null ? void 0 : _b.filter((v) => !v.color)) || [];
  const uniqueColors = [...new Set((_c = product.variants) == null ? void 0 : _c.map((v) => v.color).filter(Boolean))];
  ((_d = product.variants) == null ? void 0 : _d.filter((v) => v.size)) || [];
  ((_e = product.variants) == null ? void 0 : _e.filter((v) => !v.size)) || [];
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  const [selectedCapacity, setSelectedCapacity] = useState(null);
  const availableSizesForColor = ((_f = product.variants) == null ? void 0 : _f.filter((v) => {
    if (selectedColor) return v.color === selectedColor;
    return !v.color || uniqueColors.length === 0;
  }).map((v) => v.size).filter(Boolean)) || [];
  const uniqueSizes = [...new Set(availableSizesForColor)];
  const availableCapacitiesForColorAndSize = ((_g = product.variants) == null ? void 0 : _g.filter((v) => {
    const colorMatches = !selectedColor || v.color === selectedColor;
    const sizeMatches = !selectedSize || v.size === selectedSize;
    if (uniqueColors.length > 0 && uniqueSizes.length > 0) {
      return colorMatches && sizeMatches;
    } else if (uniqueColors.length === 0 && uniqueSizes.length > 0) {
      return sizeMatches;
    } else if (uniqueColors.length > 0 && uniqueSizes.length === 0) {
      return colorMatches;
    } else {
      return true;
    }
  }).map((v) => v.capacity).filter(Boolean)) || [];
  const uniqueCapacities = [...new Set(availableCapacitiesForColorAndSize)];
  const availableVariants = (_h = product.variants) == null ? void 0 : _h.filter((variant2) => {
    let matches = true;
    if (selectedColor && variant2.color !== selectedColor) matches = false;
    if (selectedSize && variant2.size !== selectedSize) matches = false;
    if (selectedCapacity && variant2.capacity !== selectedCapacity) matches = false;
    return matches;
  });
  useEffect(() => {
    var _a2;
    if (!((_a2 = product.variants) == null ? void 0 : _a2.length)) return;
    if (selectedVariantId) {
      const variant2 = product.variants.find((v) => v.id === selectedVariantId);
      if (variant2) {
        setSelectedVariant(variant2);
        setSelectedColor(variant2.color || null);
        setSelectedSize(variant2.size || null);
        setSelectedCapacity(variant2.capacity || null);
        return;
      }
    }
    const defaultVariant = product.variants.find((v) => v.is_default) || product.variants[0];
    setSelectedVariant(defaultVariant);
    setSelectedColor(defaultVariant.color || null);
    setSelectedSize(defaultVariant.size || null);
    setSelectedCapacity(defaultVariant.capacity || null);
  }, [product.variants, selectedVariantId]);
  useEffect(() => {
    if (selectedSize && uniqueSizes.length > 0 && !uniqueSizes.includes(selectedSize)) {
      setSelectedSize(null);
    }
    if (selectedCapacity && uniqueCapacities.length > 0 && !uniqueCapacities.includes(selectedCapacity)) {
      setSelectedCapacity(null);
    }
  }, [selectedColor, selectedSize, uniqueSizes, uniqueCapacities]);
  useEffect(() => {
    if (!(availableVariants == null ? void 0 : availableVariants.length)) return;
    const matchingVariant = availableVariants[0];
    if (matchingVariant && (!selectedVariant || selectedVariant.id !== matchingVariant.id)) {
      setSelectedVariant(matchingVariant);
      onVariantChange(matchingVariant);
    }
  }, [selectedColor, selectedSize, selectedCapacity, availableVariants]);
  if (!((_i = product.variants) == null ? void 0 : _i.length)) return null;
  return /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
    uniqueColors.length > 0 && /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h3", { className: "text-sm font-medium mb-3", children: t("color", "Color") }),
      /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: uniqueColors.map((color2) => /* @__PURE__ */ jsxs(
        Button,
        {
          type: "button",
          variant: color2 === selectedColor ? "default" : "outline",
          className: cn(
            "h-9 px-3 rounded-md",
            color2 === selectedColor ? "text-primary-foreground" : "text-foreground"
          ),
          onClick: () => {
            setSelectedColor(color2);
            setSelectedSize(null);
            setSelectedCapacity(null);
          },
          children: [
            color2 === selectedColor && /* @__PURE__ */ jsx(Check, { className: "mr-1 h-4 w-4" }),
            color2
          ]
        },
        color2
      )) })
    ] }),
    (selectedColor && uniqueSizes.length > 0 || uniqueColors.length === 0 && uniqueSizes.length > 0) && /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h3", { className: "text-sm font-medium mb-3", children: t("size", "Size") }),
      /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: uniqueSizes.map((size2) => /* @__PURE__ */ jsxs(
        Button,
        {
          type: "button",
          variant: size2 === selectedSize ? "default" : "outline",
          className: cn(
            "h-9 px-3 rounded-md",
            size2 === selectedSize ? "text-primary-foreground" : "text-foreground"
          ),
          onClick: () => {
            setSelectedSize(size2);
            setSelectedCapacity(null);
          },
          children: [
            size2 === selectedSize && /* @__PURE__ */ jsx(Check, { className: "mr-1 h-4 w-4" }),
            size2
          ]
        },
        size2
      )) })
    ] }),
    (selectedColor && selectedSize && uniqueCapacities.length > 0 || selectedColor && uniqueSizes.length === 0 && uniqueCapacities.length > 0 || selectedSize && uniqueColors.length === 0 && uniqueCapacities.length > 0 || uniqueColors.length === 0 && uniqueSizes.length === 0 && uniqueCapacities.length > 0) && /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h3", { className: "text-sm font-medium mb-3", children: t("capacity", "Capacity") }),
      /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: uniqueCapacities.map((capacity2) => /* @__PURE__ */ jsxs(
        Button,
        {
          type: "button",
          variant: capacity2 === selectedCapacity ? "default" : "outline",
          className: cn(
            "h-9 px-3 rounded-md",
            capacity2 === selectedCapacity ? "text-primary-foreground" : "text-foreground"
          ),
          onClick: () => setSelectedCapacity(capacity2),
          children: [
            capacity2 === selectedCapacity && /* @__PURE__ */ jsx(Check, { className: "mr-1 h-4 w-4" }),
            capacity2
          ]
        },
        capacity2
      )) })
    ] }),
    selectedVariant && /* @__PURE__ */ jsx("div", { className: "mt-4", children: /* @__PURE__ */ jsx(Badge, { variant: selectedVariant.quantity > 0 ? "outline" : "destructive", className: "text-sm", children: selectedVariant.quantity > 0 ? `${t("in_stock", "In Stock")} · ${selectedVariant.quantity} ${t("available", "available")}` : t("out_of_stock", "Out of Stock") }) })
  ] });
}
function Show({ product }) {
  const { t, getLocalizedField } = useI18n();
  const [quantity2, setQuantity] = useState(1);
  const getDefaultVariant = () => {
    if (!product.variants || product.variants.length === 0) return null;
    return product.variants.find((v) => v.is_default) || product.variants[0];
  };
  const [selectedVariant, setSelectedVariant] = useState(getDefaultVariant());
  useEffect(() => {
    const defaultVariant = getDefaultVariant();
    if (defaultVariant && !selectedVariant) {
      setSelectedVariant(defaultVariant);
    }
  }, [product.variants]);
  const handleVariantChange = (variant2) => {
    console.log("Selected variant:", variant2);
    setSelectedVariant(variant2);
    setQuantity(1);
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: getLocalizedField(product, "name") }),
    /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-4", children: [
        /* @__PURE__ */ jsx("div", { children: /* @__PURE__ */ jsx(
          ProductGallery,
          {
            product,
            selectedVariant: selectedVariant || void 0
          }
        ) }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col", children: [
          /* @__PURE__ */ jsx(
            ProductInfo,
            {
              product,
              selectedVariant: selectedVariant || void 0
            }
          ),
          product.variants && product.variants.length > 0 && /* @__PURE__ */ jsx("div", { className: "mb-6", children: /* @__PURE__ */ jsx(
            ProductVariantSelector,
            {
              product,
              onVariantChange: handleVariantChange,
              selectedVariantId: selectedVariant == null ? void 0 : selectedVariant.id
            }
          ) }),
          (product.isInStock || selectedVariant && selectedVariant.quantity > 0) && /* @__PURE__ */ jsxs("div", { className: "space-y-6 mb-6", children: [
            /* @__PURE__ */ jsx(
              ProductQuantitySelector,
              {
                maxQuantity: selectedVariant ? selectedVariant.quantity : product.totalQuantity || 1,
                onChange: setQuantity
              }
            ),
            /* @__PURE__ */ jsx(
              ProductActions,
              {
                product,
                quantity: quantity2,
                selectedVariant: selectedVariant || void 0
              }
            )
          ] }),
          /* @__PURE__ */ jsx("div", { className: "mb-8", children: /* @__PURE__ */ jsx(ProductDescription, { product }) })
        ] })
      ] }),
      /* @__PURE__ */ jsx(
        ProductGrid,
        {
          sectionId: "related_products",
          title: "related_products",
          emptyMessage: t(
            "no_related_products",
            "No related products found"
          )
        }
      )
    ] })
  ] });
}
const __vite_glob_0_21 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show
}, Symbol.toStringTag, { value: "Module" }));
function UpdatePasswordForm({
  className = ""
}) {
  useRef(null);
  useRef(null);
  const { toast: toast2 } = useToast();
  const { t } = useI18n();
  const {
    data,
    setData,
    errors,
    put,
    reset: resetInertia,
    processing,
    recentlySuccessful
  } = useForm({
    current_password: "",
    password: "",
    password_confirmation: ""
  });
  const formSchema2 = z.object({
    current_password: z.string().min(1, { message: t("current_password_required", "Current password is required") }),
    password: z.string().min(8, { message: t("password_min_length", "Password must be at least 8 characters") }),
    password_confirmation: z.string().min(1, { message: t("confirm_password_required", "Please confirm your password") })
  }).refine((data2) => data2.password === data2.password_confirmation, {
    message: t("passwords_dont_match", "Passwords don't match"),
    path: ["password_confirmation"]
  });
  const form = useForm$1({
    resolver: zodResolver(formSchema2),
    defaultValues: {
      current_password: "",
      password: "",
      password_confirmation: ""
    }
  });
  const onSubmit = (values) => {
    setData(values);
    put(route("password.update"), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        resetInertia();
        toast2({
          description: t("password_updated", "Password updated successfully.")
        });
      },
      onError: (errors2) => {
        if (errors2.password) {
          resetInertia("password", "password_confirmation");
          form.setError("password", { message: errors2.password });
        }
        if (errors2.current_password) {
          resetInertia("current_password");
          form.setError("current_password", { message: errors2.current_password });
        }
      }
    });
  };
  return /* @__PURE__ */ jsxs("section", { className, children: [
    /* @__PURE__ */ jsxs("header", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium", children: t("update_password", "Update Password") }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-muted-foreground", children: t("ensure_account_security", "Ensure your account is using a long, random password to stay secure.") })
    ] }),
    /* @__PURE__ */ jsx(Form, { ...form, children: /* @__PURE__ */ jsxs("form", { onSubmit: form.handleSubmit(onSubmit), className: "mt-6 space-y-6", children: [
      /* @__PURE__ */ jsx(
        FormField,
        {
          control: form.control,
          name: "current_password",
          render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
            /* @__PURE__ */ jsx(FormLabel, { htmlFor: "current_password", children: t("current_password", "Current Password") }),
            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
              Input,
              {
                id: "current_password",
                type: "password",
                autoComplete: "current-password",
                ...field
              }
            ) }),
            /* @__PURE__ */ jsx(FormMessage, {}),
            errors.current_password && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.current_password })
          ] })
        }
      ),
      /* @__PURE__ */ jsx(
        FormField,
        {
          control: form.control,
          name: "password",
          render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
            /* @__PURE__ */ jsx(FormLabel, { htmlFor: "password", children: t("new_password", "New Password") }),
            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
              Input,
              {
                id: "password",
                type: "password",
                autoComplete: "new-password",
                ...field
              }
            ) }),
            /* @__PURE__ */ jsx(FormMessage, {}),
            errors.password && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.password })
          ] })
        }
      ),
      /* @__PURE__ */ jsx(
        FormField,
        {
          control: form.control,
          name: "password_confirmation",
          render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
            /* @__PURE__ */ jsx(FormLabel, { htmlFor: "password_confirmation", children: t("confirm_password", "Confirm Password") }),
            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
              Input,
              {
                id: "password_confirmation",
                type: "password",
                autoComplete: "new-password",
                ...field
              }
            ) }),
            /* @__PURE__ */ jsx(FormMessage, {}),
            errors.password_confirmation && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.password_confirmation })
          ] })
        }
      ),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
        /* @__PURE__ */ jsx(
          Button,
          {
            type: "submit",
            disabled: processing,
            children: t("save", "Save")
          }
        ),
        recentlySuccessful && /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("saved", "Saved.") })
      ] })
    ] }) })
  ] });
}
const __vite_glob_0_24 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: UpdatePasswordForm
}, Symbol.toStringTag, { value: "Module" }));
function DeleteUserForm({
  className = ""
}) {
  const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
  const passwordInput = useRef(null);
  const { t } = useI18n();
  const formSchema2 = z.object({
    password: z.string().min(1, { message: t("password_required_deletion", "Password is required to confirm deletion") })
  });
  const form = useForm$1({
    resolver: zodResolver(formSchema2),
    defaultValues: {
      password: ""
    }
  });
  const {
    data,
    setData,
    delete: destroy,
    processing,
    reset: resetInertia,
    errors,
    clearErrors
  } = useForm({
    password: ""
  });
  const confirmUserDeletion = () => {
    setConfirmingUserDeletion(true);
  };
  const onSubmit = (values) => {
    setData(values);
    destroy(route("profile.destroy"), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => {
        var _a;
        if (errors.password) {
          form.setError("password", { message: errors.password });
        }
        (_a = passwordInput.current) == null ? void 0 : _a.focus();
      },
      onFinish: () => resetInertia()
    });
  };
  const closeModal = () => {
    setConfirmingUserDeletion(false);
    form.reset();
    clearErrors();
    resetInertia();
  };
  return /* @__PURE__ */ jsxs("section", { className: `space-y-6 ${className}`, children: [
    /* @__PURE__ */ jsxs("header", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium", children: t("delete_account", "Delete Account") }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-muted-foreground", children: t(
        "delete_account_warning",
        "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain."
      ) })
    ] }),
    /* @__PURE__ */ jsx(
      Button,
      {
        variant: "destructive",
        onClick: confirmUserDeletion,
        children: t("delete_account", "Delete Account")
      }
    ),
    /* @__PURE__ */ jsx(Dialog, { open: confirmingUserDeletion, onOpenChange: (open) => {
      if (!open) closeModal();
    }, children: /* @__PURE__ */ jsxs(DialogContent, { className: "sm:max-w-md", children: [
      /* @__PURE__ */ jsxs(DialogHeader, { children: [
        /* @__PURE__ */ jsx(DialogTitle, { children: t("confirm_delete_account", "Are you sure you want to delete your account?") }),
        /* @__PURE__ */ jsx(DialogDescription, { children: t(
          "delete_account_confirmation",
          "Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
        ) })
      ] }),
      /* @__PURE__ */ jsx(Form, { ...form, children: /* @__PURE__ */ jsxs("form", { onSubmit: form.handleSubmit(onSubmit), className: "space-y-4", children: [
        /* @__PURE__ */ jsx(
          FormField,
          {
            control: form.control,
            name: "password",
            render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
              /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
                Input,
                {
                  id: "password",
                  type: "password",
                  className: "w-full",
                  autoFocus: true,
                  placeholder: t("password", "Password"),
                  ...field,
                  ref: (e) => {
                    field.ref(e);
                    passwordInput.current = e;
                  }
                }
              ) }),
              /* @__PURE__ */ jsx(FormMessage, {}),
              errors.password && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.password })
            ] })
          }
        ),
        /* @__PURE__ */ jsxs(DialogFooter, { className: "sm:justify-end", children: [
          /* @__PURE__ */ jsx(
            Button,
            {
              type: "button",
              variant: "secondary",
              onClick: closeModal,
              children: t("cancel", "Cancel")
            }
          ),
          /* @__PURE__ */ jsx(
            Button,
            {
              type: "submit",
              variant: "destructive",
              disabled: processing,
              children: t("delete_account", "Delete Account")
            }
          )
        ] })
      ] }) })
    ] }) })
  ] });
}
const __vite_glob_0_23 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: DeleteUserForm
}, Symbol.toStringTag, { value: "Module" }));
function UpdateProfileInformation({
  mustVerifyEmail,
  status,
  className = ""
}) {
  const user = usePage().props.auth.user;
  useToast();
  const { t } = useI18n();
  const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
    name: user.name,
    email: user.email
  });
  const formSchema2 = z.object({
    name: z.string().min(2, { message: t("name_min_length", "Name must be at least 2 characters.") }),
    email: z.string().email({ message: t("valid_email", "Please enter a valid email address.") })
  });
  const form = useForm$1({
    resolver: zodResolver(formSchema2),
    defaultValues: {
      name: user.name,
      email: user.email
    }
  });
  const onSubmit = (values) => {
    setData(values);
    patch(route("profile.update"));
  };
  return /* @__PURE__ */ jsxs("section", { className, children: [
    /* @__PURE__ */ jsxs("header", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium", children: t("profile_information", "Profile Information") }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-muted-foreground", children: t("update_profile_information", "Update your account's profile information and email address.") })
    ] }),
    /* @__PURE__ */ jsx(Form, { ...form, children: /* @__PURE__ */ jsxs("form", { onSubmit: form.handleSubmit(onSubmit), className: "mt-6 space-y-6", children: [
      /* @__PURE__ */ jsx(
        FormField,
        {
          control: form.control,
          name: "name",
          render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
            /* @__PURE__ */ jsx(FormLabel, { htmlFor: "name", children: t("name", "Name") }),
            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
              Input,
              {
                id: "name",
                autoFocus: true,
                autoComplete: "name",
                ...field
              }
            ) }),
            /* @__PURE__ */ jsx(FormMessage, {}),
            errors.name && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.name })
          ] })
        }
      ),
      /* @__PURE__ */ jsx(
        FormField,
        {
          control: form.control,
          name: "email",
          render: ({ field }) => /* @__PURE__ */ jsxs(FormItem, { children: [
            /* @__PURE__ */ jsx(FormLabel, { htmlFor: "email", children: t("email", "Email") }),
            /* @__PURE__ */ jsx(FormControl, { children: /* @__PURE__ */ jsx(
              Input,
              {
                id: "email",
                type: "email",
                autoComplete: "username",
                ...field
              }
            ) }),
            /* @__PURE__ */ jsx(FormMessage, {}),
            errors.email && /* @__PURE__ */ jsx("p", { className: "text-sm text-destructive", children: errors.email })
          ] })
        }
      ),
      mustVerifyEmail && user.email_verified_at === null && /* @__PURE__ */ jsx(Alert, { variant: "default", className: "mt-2", children: /* @__PURE__ */ jsxs(AlertDescription, { children: [
        t("verify_email_description", "Your email address is unverified."),
        " ",
        /* @__PURE__ */ jsx(
          Link,
          {
            href: route("verification.send"),
            method: "post",
            as: "button",
            className: "text-primary underline hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2",
            children: t("click_resend_verification", "Click here to re-send the verification email.")
          }
        )
      ] }) }),
      status === "verification-link-sent" && /* @__PURE__ */ jsx(Alert, { variant: "default", className: "mt-2 border-green-500 bg-green-50 dark:bg-green-950/50", children: /* @__PURE__ */ jsx(AlertDescription, { className: "text-sm font-medium", children: t("verification_link_sent", "A new verification link has been sent to your email address.") }) }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
        /* @__PURE__ */ jsx(
          Button,
          {
            type: "submit",
            disabled: processing,
            children: t("save", "Save")
          }
        ),
        recentlySuccessful && /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("saved", "Saved.") })
      ] })
    ] }) })
  ] });
}
const __vite_glob_0_25 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: UpdateProfileInformation
}, Symbol.toStringTag, { value: "Module" }));
const DropdownMenu = DropdownMenuPrimitive.Root;
const DropdownMenuTrigger = DropdownMenuPrimitive.Trigger;
const DropdownMenuSubTrigger = React.forwardRef(({ className, inset, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  DropdownMenuPrimitive.SubTrigger,
  {
    ref,
    className: cn(
      "flex cursor-default select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none focus:bg-accent data-[state=open]:bg-accent [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0",
      inset && "pl-8",
      className
    ),
    ...props,
    children: [
      children,
      /* @__PURE__ */ jsx(ChevronRight, { className: "ml-auto" })
    ]
  }
));
DropdownMenuSubTrigger.displayName = DropdownMenuPrimitive.SubTrigger.displayName;
const DropdownMenuSubContent = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DropdownMenuPrimitive.SubContent,
  {
    ref,
    className: cn(
      "z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-lg data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 origin-[--radix-dropdown-menu-content-transform-origin]",
      className
    ),
    ...props
  }
));
DropdownMenuSubContent.displayName = DropdownMenuPrimitive.SubContent.displayName;
const DropdownMenuContent = React.forwardRef(({ className, sideOffset = 4, ...props }, ref) => /* @__PURE__ */ jsx(DropdownMenuPrimitive.Portal, { children: /* @__PURE__ */ jsx(
  DropdownMenuPrimitive.Content,
  {
    ref,
    sideOffset,
    className: cn(
      "z-50 max-h-[var(--radix-dropdown-menu-content-available-height)] min-w-[8rem] overflow-y-auto overflow-x-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md",
      "data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 origin-[--radix-dropdown-menu-content-transform-origin]",
      className
    ),
    ...props
  }
) }));
DropdownMenuContent.displayName = DropdownMenuPrimitive.Content.displayName;
const DropdownMenuItem = React.forwardRef(({ className, inset, ...props }, ref) => /* @__PURE__ */ jsx(
  DropdownMenuPrimitive.Item,
  {
    ref,
    className: cn(
      "relative flex cursor-default select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&>svg]:size-4 [&>svg]:shrink-0",
      inset && "pl-8",
      className
    ),
    ...props
  }
));
DropdownMenuItem.displayName = DropdownMenuPrimitive.Item.displayName;
const DropdownMenuCheckboxItem = React.forwardRef(({ className, children, checked, ...props }, ref) => /* @__PURE__ */ jsxs(
  DropdownMenuPrimitive.CheckboxItem,
  {
    ref,
    className: cn(
      "relative flex cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
      className
    ),
    checked,
    ...props,
    children: [
      /* @__PURE__ */ jsx("span", { className: "absolute left-2 flex h-3.5 w-3.5 items-center justify-center", children: /* @__PURE__ */ jsx(DropdownMenuPrimitive.ItemIndicator, { children: /* @__PURE__ */ jsx(Check, { className: "h-4 w-4" }) }) }),
      children
    ]
  }
));
DropdownMenuCheckboxItem.displayName = DropdownMenuPrimitive.CheckboxItem.displayName;
const DropdownMenuRadioItem = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  DropdownMenuPrimitive.RadioItem,
  {
    ref,
    className: cn(
      "relative flex cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
      className
    ),
    ...props,
    children: [
      /* @__PURE__ */ jsx("span", { className: "absolute left-2 flex h-3.5 w-3.5 items-center justify-center", children: /* @__PURE__ */ jsx(DropdownMenuPrimitive.ItemIndicator, { children: /* @__PURE__ */ jsx(Circle, { className: "h-2 w-2 fill-current" }) }) }),
      children
    ]
  }
));
DropdownMenuRadioItem.displayName = DropdownMenuPrimitive.RadioItem.displayName;
const DropdownMenuLabel = React.forwardRef(({ className, inset, ...props }, ref) => /* @__PURE__ */ jsx(
  DropdownMenuPrimitive.Label,
  {
    ref,
    className: cn(
      "px-2 py-1.5 text-sm font-semibold",
      inset && "pl-8",
      className
    ),
    ...props
  }
));
DropdownMenuLabel.displayName = DropdownMenuPrimitive.Label.displayName;
const DropdownMenuSeparator = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DropdownMenuPrimitive.Separator,
  {
    ref,
    className: cn("-mx-1 my-1 h-px bg-muted", className),
    ...props
  }
));
DropdownMenuSeparator.displayName = DropdownMenuPrimitive.Separator.displayName;
function LanguageSwitcher({ className }) {
  const { currentLocale, languages: languages2, setLanguage } = useI18n();
  const currentLanguage = languages2.find((lang) => lang.code === currentLocale);
  return /* @__PURE__ */ jsxs(DropdownMenu, { children: [
    /* @__PURE__ */ jsx(DropdownMenuTrigger, { asChild: true, children: /* @__PURE__ */ jsxs(
      Button,
      {
        variant: "outline",
        className: `flex items-center gap-2 ${className}`,
        "aria-label": "Switch language",
        children: [
          /* @__PURE__ */ jsx(Languages, { className: "h-4 w-4" }),
          /* @__PURE__ */ jsx("span", { className: "text-sm font-medium", children: (currentLanguage == null ? void 0 : currentLanguage.name) || "Language" })
        ]
      }
    ) }),
    /* @__PURE__ */ jsx(DropdownMenuContent, { align: "end", children: languages2.map((language2) => /* @__PURE__ */ jsx(
      DropdownMenuItem,
      {
        className: currentLocale === language2.code ? "bg-muted" : "",
        onClick: () => setLanguage(language2.code),
        children: language2.name
      },
      language2.code
    )) })
  ] });
}
function Edit({
  mustVerifyEmail,
  status
}) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("profile", "Profile") }),
    /* @__PURE__ */ jsx(
      PageTitle,
      {
        title: t("profile", "Profile"),
        icon: /* @__PURE__ */ jsx(UserCog, { className: "h-6 w-6 text-primary" })
      }
    ),
    /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8", children: [
      /* @__PURE__ */ jsx(Card, { className: "p-4 sm:p-8", children: /* @__PURE__ */ jsx(
        UpdateProfileInformation,
        {
          mustVerifyEmail,
          status,
          className: "max-w-xl"
        }
      ) }),
      /* @__PURE__ */ jsx(Card, { className: "p-4 sm:p-8", children: /* @__PURE__ */ jsx(UpdatePasswordForm, { className: "max-w-xl" }) }),
      /* @__PURE__ */ jsx(Card, { className: "p-4 sm:p-8", children: /* @__PURE__ */ jsxs("div", { className: "max-w-xl", children: [
        /* @__PURE__ */ jsxs("header", { className: "mb-4", children: [
          /* @__PURE__ */ jsxs("h2", { className: "text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2", children: [
            /* @__PURE__ */ jsx(Languages, { className: "h-5 w-5 text-primary" }),
            t("language_preferences", "Language Preferences")
          ] }),
          /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-600 dark:text-gray-400", children: t("language_preferences_description", "Choose your preferred language for the interface.") })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
          /* @__PURE__ */ jsxs("span", { className: "text-sm font-medium", children: [
            t("select_language", "Select Language"),
            ":"
          ] }),
          /* @__PURE__ */ jsx(LanguageSwitcher, {})
        ] })
      ] }) }),
      /* @__PURE__ */ jsx(Card, { className: "p-4 sm:p-8", children: /* @__PURE__ */ jsx(DeleteUserForm, { className: "max-w-xl" }) })
    ] })
  ] });
}
const __vite_glob_0_22 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Edit
}, Symbol.toStringTag, { value: "Module" }));
const Sheet = DialogPrimitive.Root;
const SheetTrigger = DialogPrimitive.Trigger;
const SheetClose = DialogPrimitive.Close;
const SheetPortal = DialogPrimitive.Portal;
const SheetOverlay = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DialogPrimitive.Overlay,
  {
    className: cn(
      "fixed inset-0 z-50 bg-black/80  data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0",
      className
    ),
    ...props,
    ref
  }
));
SheetOverlay.displayName = DialogPrimitive.Overlay.displayName;
const sheetVariants = cva(
  "fixed z-50 gap-4 bg-background p-6 shadow-lg transition ease-in-out data-[state=closed]:duration-300 data-[state=open]:duration-500 data-[state=open]:animate-in data-[state=closed]:animate-out",
  {
    variants: {
      side: {
        top: "inset-x-0 top-0 border-b data-[state=closed]:slide-out-to-top data-[state=open]:slide-in-from-top",
        bottom: "inset-x-0 bottom-0 border-t data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom",
        left: "inset-y-0 left-0 h-full w-3/4 border-r data-[state=closed]:slide-out-to-left data-[state=open]:slide-in-from-left sm:max-w-sm",
        right: "inset-y-0 right-0 h-full w-3/4 border-l data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right sm:max-w-sm"
      }
    },
    defaultVariants: {
      side: "right"
    }
  }
);
const SheetContent = React.forwardRef(({ side = "right", className, children, ...props }, ref) => /* @__PURE__ */ jsxs(SheetPortal, { children: [
  /* @__PURE__ */ jsx(SheetOverlay, {}),
  /* @__PURE__ */ jsxs(
    DialogPrimitive.Content,
    {
      ref,
      className: cn(sheetVariants({ side }), className),
      ...props,
      children: [
        /* @__PURE__ */ jsxs(DialogPrimitive.Close, { className: "absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-secondary", children: [
          /* @__PURE__ */ jsx(X, { className: "h-4 w-4" }),
          /* @__PURE__ */ jsx("span", { className: "sr-only", children: "Close" })
        ] }),
        children
      ]
    }
  )
] }));
SheetContent.displayName = DialogPrimitive.Content.displayName;
const SheetHeader = ({
  className,
  ...props
}) => /* @__PURE__ */ jsx(
  "div",
  {
    className: cn(
      "flex flex-col space-y-2 text-center sm:text-left",
      className
    ),
    ...props
  }
);
SheetHeader.displayName = "SheetHeader";
const SheetFooter = ({
  className,
  ...props
}) => /* @__PURE__ */ jsx(
  "div",
  {
    className: cn(
      "flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2",
      className
    ),
    ...props
  }
);
SheetFooter.displayName = "SheetFooter";
const SheetTitle = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DialogPrimitive.Title,
  {
    ref,
    className: cn("text-lg font-semibold text-foreground", className),
    ...props
  }
));
SheetTitle.displayName = DialogPrimitive.Title.displayName;
const SheetDescription = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  DialogPrimitive.Description,
  {
    ref,
    className: cn("text-sm text-muted-foreground", className),
    ...props
  }
));
SheetDescription.displayName = DialogPrimitive.Description.displayName;
const Collapsible = CollapsiblePrimitive.Root;
const CollapsibleTrigger = CollapsiblePrimitive.CollapsibleTrigger;
const CollapsibleContent = CollapsiblePrimitive.CollapsibleContent;
const CategoryItem = ({
  category: category2,
  localSelectedCategories,
  toggleCategory,
  level,
  getName
}) => {
  const [isOpen, setIsOpen] = useState(true);
  const hasChildren = category2.children && category2.children.length > 0;
  const { direction } = useI18n();
  return /* @__PURE__ */ jsx("div", { className: "space-y-1", children: hasChildren ? /* @__PURE__ */ jsxs(Collapsible, { open: isOpen, onOpenChange: setIsOpen, dir: direction, children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-center space-x-2 rtl:space-x-reverse", children: [
      /* @__PURE__ */ jsx("div", { style: { width: `${level * 12}px` } }),
      /* @__PURE__ */ jsx(CollapsibleTrigger, { className: "focus:outline-none", children: isOpen ? /* @__PURE__ */ jsx(ChevronDown, { className: "h-4 w-4" }) : direction === "rtl" ? /* @__PURE__ */ jsx(ChevronLeft, { className: "h-4 w-4" }) : /* @__PURE__ */ jsx(ChevronRight, { className: "h-4 w-4" }) }),
      /* @__PURE__ */ jsx(
        Checkbox,
        {
          id: `category-${category2.id}`,
          checked: localSelectedCategories.includes(
            category2.id.toString()
          ),
          onCheckedChange: () => toggleCategory(category2.id.toString())
        }
      ),
      /* @__PURE__ */ jsx(
        Label,
        {
          htmlFor: `category-${category2.id}`,
          className: "text-sm cursor-pointer",
          children: getName(category2)
        }
      )
    ] }),
    /* @__PURE__ */ jsx(CollapsibleContent, { children: /* @__PURE__ */ jsx("div", { className: "space-y-1 ml-4 rtl:ml-0 rtl:mr-4", children: category2.children.map((child) => /* @__PURE__ */ jsx(
      CategoryItem,
      {
        category: child,
        localSelectedCategories,
        toggleCategory,
        level: level + 1,
        getName
      },
      child.id
    )) }) })
  ] }) : /* @__PURE__ */ jsxs("div", { className: "flex items-center space-x-2 rtl:space-x-reverse", children: [
    /* @__PURE__ */ jsx("div", { style: { width: `${level * 12}px` } }),
    /* @__PURE__ */ jsx("div", { className: "w-4" }),
    /* @__PURE__ */ jsx(
      Checkbox,
      {
        id: `category-${category2.id}`,
        checked: localSelectedCategories.includes(
          category2.id.toString()
        ),
        onCheckedChange: () => toggleCategory(category2.id.toString())
      }
    ),
    /* @__PURE__ */ jsx(
      Label,
      {
        htmlFor: `category-${category2.id}`,
        className: "text-sm cursor-pointer",
        children: getName(category2)
      }
    )
  ] }) });
};
function FilterModal({
  brands: brands2 = [],
  categories: categories2 = [],
  priceRange = { min: 0, max: 1e3 },
  selectedBrands = [],
  selectedCategories = [],
  minPrice = null,
  maxPrice = null,
  query = "",
  sortBy = "newest"
}) {
  const { t, currentLocale, direction } = useI18n();
  const isRTL = currentLocale === "ar";
  console.log(brands2, categories2, priceRange, selectedBrands, selectedCategories, minPrice, maxPrice, query, sortBy);
  const [localSelectedBrands, setLocalSelectedBrands] = useState(
    selectedBrands || []
  );
  const [localSelectedCategories, setLocalSelectedCategories] = useState(selectedCategories || []);
  const [localPriceRange, setLocalPriceRange] = useState([
    minPrice !== null ? minPrice : priceRange.min,
    maxPrice !== null ? maxPrice : priceRange.max
  ]);
  const [isOpen, setIsOpen] = useState(false);
  const [activeFiltersCount, setActiveFiltersCount] = useState(0);
  useEffect(() => {
    setLocalSelectedBrands(selectedBrands || []);
    setLocalSelectedCategories(selectedCategories || []);
    setLocalPriceRange([
      minPrice !== null ? minPrice : priceRange.min,
      maxPrice !== null ? maxPrice : priceRange.max
    ]);
    let count2 = 0;
    if (selectedBrands == null ? void 0 : selectedBrands.length) count2 += selectedBrands.length;
    if (selectedCategories == null ? void 0 : selectedCategories.length) count2 += selectedCategories.length;
    if (minPrice !== null || maxPrice !== null) count2 += 1;
    setActiveFiltersCount(count2);
  }, [selectedBrands, selectedCategories, minPrice, maxPrice, priceRange]);
  const toggleBrand = (brandId) => {
    setLocalSelectedBrands((current) => {
      if (current.includes(brandId)) {
        return current.filter((id) => id !== brandId);
      } else {
        return [...current, brandId];
      }
    });
  };
  const toggleCategory = (categoryId) => {
    setLocalSelectedCategories((current) => {
      if (current.includes(categoryId)) {
        return current.filter((id) => id !== categoryId);
      } else {
        return [...current, categoryId];
      }
    });
  };
  const applyFilters = () => {
    router.get("/search", {
      q: query,
      brands: localSelectedBrands,
      categories: localSelectedCategories,
      min_price: localPriceRange[0] !== priceRange.min ? localPriceRange[0] : null,
      max_price: localPriceRange[1] !== priceRange.max ? localPriceRange[1] : null,
      sort_by: sortBy
    });
    setIsOpen(false);
  };
  const resetFilters = () => {
    setLocalSelectedBrands([]);
    setLocalSelectedCategories([]);
    setLocalPriceRange([priceRange.min, priceRange.max]);
  };
  const getName = (item) => {
    return isRTL ? item.name_ar : item.name_en;
  };
  const hasActiveFilters = localSelectedBrands.length > 0 || localSelectedCategories.length > 0 || localPriceRange[0] !== priceRange.min || localPriceRange[1] !== priceRange.max;
  return /* @__PURE__ */ jsxs(Sheet, { open: isOpen, onOpenChange: setIsOpen, children: [
    /* @__PURE__ */ jsx(SheetTrigger, { asChild: true, children: /* @__PURE__ */ jsxs(
      Button,
      {
        variant: "outline",
        className: "flex items-center gap-2",
        "aria-label": t("filters", "Filters"),
        children: [
          /* @__PURE__ */ jsx(FilterIcon, { className: "h-4 w-4" }),
          /* @__PURE__ */ jsx("span", { children: t("filters", "Filters") }),
          activeFiltersCount > 0 && /* @__PURE__ */ jsx(Badge, { variant: "secondary", className: "text-xs", children: activeFiltersCount })
        ]
      }
    ) }),
    /* @__PURE__ */ jsxs(
      SheetContent,
      {
        side: isRTL ? "left" : "right",
        className: "w-full sm:max-w-md flex flex-col",
        dir: direction,
        children: [
          /* @__PURE__ */ jsx(SheetHeader, { className: "space-y-1 mt-4", children: /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
            /* @__PURE__ */ jsx(SheetTitle, { children: t("filters", "Filters") }),
            hasActiveFilters && /* @__PURE__ */ jsx(
              Button,
              {
                variant: "secondary",
                size: "sm",
                onClick: resetFilters,
                children: t("reset_all", "Reset all")
              }
            )
          ] }) }),
          /* @__PURE__ */ jsx(ScrollArea, { className: "flex-1 mt-6 pr-4", dir: direction, children: /* @__PURE__ */ jsxs("div", { className: "space-y-6", children: [
            brands2.length > 0 && /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-lg font-medium", children: t("brands", "Brands") }),
              /* @__PURE__ */ jsx("div", { className: "space-y-2", children: brands2.map((brand) => /* @__PURE__ */ jsxs(
                "div",
                {
                  className: "flex items-center space-x-2 rtl:space-x-reverse",
                  children: [
                    /* @__PURE__ */ jsx(
                      Checkbox,
                      {
                        id: `brand-${brand.id}`,
                        checked: localSelectedBrands.includes(
                          brand.id.toString()
                        ),
                        onCheckedChange: () => toggleBrand(
                          brand.id.toString()
                        )
                      }
                    ),
                    /* @__PURE__ */ jsx(
                      Label,
                      {
                        htmlFor: `brand-${brand.id}`,
                        className: "text-sm cursor-pointer",
                        children: getName(brand)
                      }
                    )
                  ]
                },
                brand.id
              )) }),
              brands2.length === 0 && /* @__PURE__ */ jsx("div", { className: "py-2 text-sm text-muted-foreground", children: t(
                "no_brands_available",
                "No brands available"
              ) })
            ] }),
            /* @__PURE__ */ jsx(Separator, { className: "my-6" }),
            categories2.length > 0 && /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-lg font-medium", children: t("categories", "Categories") }),
              /* @__PURE__ */ jsx("div", { className: "space-y-2", children: categories2.map((category2) => /* @__PURE__ */ jsx(
                CategoryItem,
                {
                  category: category2,
                  localSelectedCategories,
                  toggleCategory,
                  level: 0,
                  getName
                },
                category2.id
              )) }),
              categories2.length === 0 && /* @__PURE__ */ jsx("div", { className: "py-2 text-sm text-muted-foreground", children: t(
                "no_categories_available",
                "No categories available"
              ) })
            ] }),
            /* @__PURE__ */ jsx(Separator, { className: "my-6" }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-lg font-medium", children: t("price_range", "Price Range") }),
              /* @__PURE__ */ jsxs("div", { className: "px-4", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between gap-4", children: [
                  /* @__PURE__ */ jsxs("div", { className: "space-y-2 w-full", children: [
                    /* @__PURE__ */ jsx(
                      Label,
                      {
                        htmlFor: "min-price",
                        className: "text-sm",
                        children: t("min_price", "Min Price")
                      }
                    ),
                    /* @__PURE__ */ jsx(
                      Input,
                      {
                        id: "min-price",
                        type: "number",
                        value: localPriceRange[0],
                        onChange: (e) => {
                          const value = Number(
                            e.target.value
                          );
                          setLocalPriceRange([
                            value,
                            localPriceRange[1]
                          ]);
                        },
                        className: "w-full"
                      }
                    )
                  ] }),
                  /* @__PURE__ */ jsx("span", { className: "text-muted-foreground mx-1 mt-8", children: "—" }),
                  /* @__PURE__ */ jsxs("div", { className: "space-y-2 w-full", children: [
                    /* @__PURE__ */ jsx(
                      Label,
                      {
                        htmlFor: "max-price",
                        className: "text-sm",
                        children: t("max_price", "Max Price")
                      }
                    ),
                    /* @__PURE__ */ jsx(
                      Input,
                      {
                        id: "max-price",
                        type: "number",
                        value: localPriceRange[1],
                        onChange: (e) => {
                          const value = Number(
                            e.target.value
                          );
                          setLocalPriceRange([
                            localPriceRange[0],
                            value
                          ]);
                        },
                        className: "w-full"
                      }
                    )
                  ] })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mt-4", children: [
                  /* @__PURE__ */ jsx(
                    Button,
                    {
                      variant: "outline",
                      size: "sm",
                      onClick: () => setLocalPriceRange([
                        priceRange.min,
                        priceRange.max
                      ]),
                      className: "text-xs h-8",
                      children: t("reset_price", "Reset Price")
                    }
                  ),
                  /* @__PURE__ */ jsx("span", { className: "text-xs text-muted-foreground", children: t(
                    "price_range_from_to",
                    "Range: {{min}} - {{max}}",
                    {
                      min: priceRange.min,
                      max: priceRange.max
                    }
                  ) })
                ] })
              ] })
            ] })
          ] }) }),
          /* @__PURE__ */ jsx(SheetFooter, { className: "pt-4 sm:pt-0", children: /* @__PURE__ */ jsx(Button, { onClick: applyFilters, className: "w-full", children: t("apply_filters", "Apply Filters") }) })
        ]
      }
    )
  ] });
}
function SortSelector({
  sortBy = "newest",
  query = "",
  selectedBrands = [],
  selectedCategories = [],
  minPrice = null,
  maxPrice = null
}) {
  const { t, currentLocale, direction } = useI18n();
  const handleSortChange = (value) => {
    router.get("/search", {
      q: query,
      brands: selectedBrands,
      categories: selectedCategories,
      min_price: minPrice,
      max_price: maxPrice,
      sort_by: value
    });
  };
  const sortOptions = [
    { value: "newest", label: t("newest", "Newest"), icon: Clock },
    { value: "price_low_high", label: t("price_low_high", "Price: Low to High"), icon: ArrowDown01 },
    { value: "price_high_low", label: t("price_high_low", "Price: High to Low"), icon: ArrowDown10 },
    { value: "name_a_z", label: t("name_a_z", "Name: A to Z"), icon: ArrowDownAZ },
    { value: "name_z_a", label: t("name_z_a", "Name: Z to A"), icon: ArrowDownZA }
  ];
  return /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", dir: direction, children: [
    /* @__PURE__ */ jsxs("span", { className: "text-sm text-muted-foreground hidden md:inline-block", children: [
      t("sort_by", "Sort by"),
      ":"
    ] }),
    /* @__PURE__ */ jsxs(Select, { value: sortBy, onValueChange: handleSortChange, dir: direction, children: [
      /* @__PURE__ */ jsx(SelectTrigger, { className: "w-[150px] sm:w-[180px]", children: /* @__PURE__ */ jsx(SelectValue, { placeholder: t("sort_by", "Sort by") }) }),
      /* @__PURE__ */ jsx(SelectContent, { children: /* @__PURE__ */ jsx(SelectGroup, { children: sortOptions.map((option) => {
        const Icon = option.icon;
        return /* @__PURE__ */ jsx(
          SelectItem,
          {
            value: option.value,
            className: "flex items-center gap-2",
            children: /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
              /* @__PURE__ */ jsx(Icon, { className: "h-4 w-4" }),
              /* @__PURE__ */ jsx("span", { children: option.label })
            ] })
          },
          option.value
        );
      }) }) })
    ] })
  ] });
}
function SearchSuggestions({
  query,
  onSuggestionClick,
  isOpen,
  onClose,
  className
}) {
  const { t, direction, getLocalizedField } = useI18n();
  const [suggestions, setSuggestions] = useState([]);
  const [loading2, setLoading] = useState(false);
  const isRTL = direction === "rtl";
  const getLocalizedValue = (obj, fieldName) => {
    return getLocalizedField(obj, fieldName) || obj[`${fieldName}_en`] || obj[`${fieldName}_ar`] || "";
  };
  useEffect(() => {
    if (!query || query.length < 2) {
      setSuggestions([]);
      return;
    }
    const debounceTimer = setTimeout(async () => {
      setLoading(true);
      try {
        const response = await fetch(
          `/search/suggestions?q=${encodeURIComponent(query)}`
        );
        const data = await response.json();
        setSuggestions(data.suggestions || []);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        setSuggestions([]);
      } finally {
        setLoading(false);
      }
    }, 300);
    return () => clearTimeout(debounceTimer);
  }, [query]);
  const handleSuggestionClick = (suggestion) => {
    if (onSuggestionClick) onSuggestionClick(suggestion);
    onClose();
    router.visit(`/products/${suggestion.id}`);
  };
  const handleViewAllResults = () => {
    router.get("/search", { q: query });
    onClose();
  };
  if (!isOpen || !loading2 && suggestions.length === 0 && query.length >= 2) {
    return null;
  }
  return /* @__PURE__ */ jsx(
    "div",
    {
      className: cn(
        "absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-50 max-h-96 overflow-y-auto",
        className
      ),
      children: loading2 ? /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-center py-4", children: [
        /* @__PURE__ */ jsx(Loader2, { className: "h-4 w-4 animate-spin mr-2" }),
        /* @__PURE__ */ jsx("span", { className: "text-sm text-muted-foreground", children: t("searching", "Searching...") })
      ] }) : /* @__PURE__ */ jsx(Fragment, { children: suggestions.length > 0 && /* @__PURE__ */ jsxs("div", { className: "py-2", children: [
        suggestions.map((suggestion) => /* @__PURE__ */ jsxs(
          "button",
          {
            onClick: () => handleSuggestionClick(suggestion),
            className: "w-full px-4 py-3 text-start hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-3",
            children: [
              /* @__PURE__ */ jsx(
                Image,
                {
                  src: suggestion.featured_image,
                  alt: getLocalizedValue(
                    suggestion,
                    "name"
                  ),
                  className: "w-10 h-10 object-cover rounded-md flex-shrink-0",
                  fallback: /* @__PURE__ */ jsx("div", { className: "w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-md flex-shrink-0 flex items-center justify-center", children: /* @__PURE__ */ jsx(Search, { className: "h-4 w-4 text-gray-400" }) }),
                  useDefaultFallback: false
                }
              ),
              /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
                /* @__PURE__ */ jsx("div", { className: "font-medium text-sm truncate", children: getLocalizedValue(
                  suggestion,
                  "name"
                ) }),
                /* @__PURE__ */ jsxs("div", { className: "text-xs text-muted-foreground truncate", children: [
                  suggestion.brand && /* @__PURE__ */ jsx("span", { children: getLocalizedValue(
                    suggestion.brand,
                    "name"
                  ) }),
                  suggestion.brand && suggestion.category && " • ",
                  suggestion.category && /* @__PURE__ */ jsx("span", { children: getLocalizedValue(
                    suggestion.category,
                    "name"
                  ) })
                ] })
              ] }),
              /* @__PURE__ */ jsx("div", { className: "text-sm font-medium text-primary", children: new Intl.NumberFormat(
                isRTL ? "ar-EG" : "en-US",
                {
                  style: "currency",
                  currency: "EGP"
                }
              ).format(suggestion.price) })
            ]
          },
          suggestion.id
        )),
        query.length >= 2 && /* @__PURE__ */ jsxs("div", { className: "border-t border-gray-200 dark:border-gray-700 pt-2", children: [
          " ",
          /* @__PURE__ */ jsxs(
            Button,
            {
              onClick: handleViewAllResults,
              variant: "ghost",
              className: "w-full justify-start px-4 py-3 text-sm",
              children: [
                /* @__PURE__ */ jsx(Search, { className: "h-4 w-4 mr-2" }),
                t(
                  "view_all_results_for",
                  "View all results for '{{query}}'"
                ).replace("{{query}}", query)
              ]
            }
          )
        ] })
      ] }) })
    }
  );
}
const defaultFilters = {
  brands: [],
  categories: [],
  priceRange: { min: 0, max: 1e3 },
  selectedBrands: [],
  selectedCategories: [],
  minPrice: null,
  maxPrice: null,
  sortBy: "newest"
};
function Results({
  query,
  filters: filters2 = defaultFilters
}) {
  const { t } = useI18n();
  const [searchQuery, setSearchQuery] = useState(query || "");
  const [showSuggestions, setShowSuggestions] = useState(false);
  const searchRef = useRef(null);
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (searchRef.current && !searchRef.current.contains(event.target)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);
  const safeFilters = { ...defaultFilters, ...filters2 };
  const getBrandsForFilter = safeFilters.brands ?? [];
  const getCategoriesForFilter = safeFilters.categories ?? [];
  const getSelectedBrands = () => {
    return safeFilters.selectedBrands || [];
  };
  const getSelectedCategories = () => {
    return safeFilters.selectedCategories || [];
  };
  const getMinPrice = () => {
    return safeFilters.minPrice !== void 0 ? safeFilters.minPrice : null;
  };
  const getMaxPrice = () => {
    return safeFilters.maxPrice !== void 0 ? safeFilters.maxPrice : null;
  };
  const getSortBy = () => {
    return safeFilters.sortBy || "newest";
  };
  const getPriceRange = () => {
    return safeFilters.priceRange || { min: 0, max: 1e3 };
  };
  const hasFiltersApplied = () => {
    return Boolean(
      safeFilters.selectedBrands && safeFilters.selectedBrands.length > 0 || safeFilters.selectedCategories && safeFilters.selectedCategories.length > 0 || safeFilters.minPrice !== null || safeFilters.maxPrice !== null
    );
  };
  const handleSearch = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      router.get("/search", {
        q: searchQuery.trim(),
        brands: safeFilters.selectedBrands,
        categories: safeFilters.selectedCategories,
        min_price: safeFilters.minPrice,
        max_price: safeFilters.maxPrice,
        sort_by: safeFilters.sortBy
      });
    }
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("search_results", "Search Results") }),
    /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6", children: [
        /* @__PURE__ */ jsxs("div", { children: [
          /* @__PURE__ */ jsx("h1", { className: "text-2xl md:text-3xl font-bold mb-2", children: t("search_results", "Search Results") }),
          query && /* @__PURE__ */ jsx("p", { className: "text-muted-foreground", children: t("results_for", "Results for '{{query}}'").replace("{{query}}", query) })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "w-full md:w-auto", children: /* @__PURE__ */ jsx(
          "form",
          {
            onSubmit: handleSearch,
            className: "flex w-full md:w-80",
            children: /* @__PURE__ */ jsxs("div", { ref: searchRef, className: "relative flex-1", children: [
              /* @__PURE__ */ jsx(
                Input,
                {
                  type: "text",
                  value: searchQuery,
                  onChange: (e) => {
                    setSearchQuery(e.target.value);
                    setShowSuggestions(true);
                  },
                  onFocus: () => setShowSuggestions(true),
                  placeholder: t(
                    "search_placeholder",
                    "Search products..."
                  ),
                  className: "w-full pr-8"
                }
              ),
              /* @__PURE__ */ jsx(
                Button,
                {
                  type: "submit",
                  size: "icon",
                  variant: "ghost",
                  className: "absolute ltr:right-0 rtl:left-0 top-0 h-full",
                  children: /* @__PURE__ */ jsx(Search, { className: "h-4 w-4" })
                }
              ),
              /* @__PURE__ */ jsx(
                SearchSuggestions,
                {
                  query: searchQuery,
                  isOpen: showSuggestions,
                  onClose: () => setShowSuggestions(false),
                  onSuggestionClick: () => setShowSuggestions(false)
                }
              )
            ] })
          }
        ) })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap justify-between items-center gap-4 mb-6", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2", children: [
          /* @__PURE__ */ jsx(
            FilterModal,
            {
              brands: getBrandsForFilter,
              categories: getCategoriesForFilter,
              priceRange: getPriceRange(),
              selectedBrands: getSelectedBrands(),
              selectedCategories: getSelectedCategories(),
              minPrice: getMinPrice(),
              maxPrice: getMaxPrice(),
              query,
              sortBy: getSortBy()
            }
          ),
          /* @__PURE__ */ jsx("div", { className: "text-sm text-muted-foreground", children: hasFiltersApplied() && /* @__PURE__ */ jsx("span", { children: t("filters_applied", "Filters applied") }) })
        ] }),
        /* @__PURE__ */ jsx(
          SortSelector,
          {
            sortBy: getSortBy(),
            query,
            selectedBrands: getSelectedBrands(),
            selectedCategories: getSelectedCategories(),
            minPrice: getMinPrice(),
            maxPrice: getMaxPrice()
          }
        )
      ] }),
      /* @__PURE__ */ jsx(
        ProductGrid,
        {
          sectionId: "search_results_products",
          emptyMessage: t(
            "try_different_keywords",
            "Try different keywords or filters"
          ),
          className: "pt-0",
          viewType: "grid"
        }
      )
    ] })
  ] });
}
const __vite_glob_0_26 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Results
}, Symbol.toStringTag, { value: "Module" }));
function Index({ items: items2 }) {
  const { t } = useI18n();
  const [removing2, setRemoving] = useState({});
  const [clearingList, setClearingList] = useState(false);
  const handleRemoveItem = (productId) => {
    setRemoving((prev) => ({ ...prev, [productId]: true }));
    router.delete(
      route("wishlist.remove", productId),
      {
        preserveScroll: true,
        onFinish: () => {
          setRemoving((prev) => ({ ...prev, [productId]: false }));
        }
      }
    );
  };
  const handleClearList = () => {
    if (items2.length === 0) return;
    setClearingList(true);
    router.delete(
      route("wishlist.clear"),
      {
        preserveScroll: true,
        onFinish: () => {
          setClearingList(false);
        }
      }
    );
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head, { title: t("wishlist", "Wishlist") }),
    /* @__PURE__ */ jsxs("div", { className: "container mt-4", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-6", children: [
        /* @__PURE__ */ jsxs("h1", { className: "text-2xl md:text-3xl font-bold flex items-center gap-2", children: [
          /* @__PURE__ */ jsx(Heart, { className: "h-6 w-6" }),
          t("wishlist", "Wishlist")
        ] }),
        items2.length > 0 && /* @__PURE__ */ jsxs(
          Button,
          {
            variant: "outline",
            size: "sm",
            onClick: handleClearList,
            disabled: clearingList,
            className: "text-destructive hover:text-destructive-foreground hover:bg-destructive",
            children: [
              /* @__PURE__ */ jsx(Trash2, { className: "h-4 w-4 mr-2" }),
              clearingList ? t("clearing", "Clearing...") : t("clear_wishlist", "Clear Wishlist")
            ]
          }
        )
      ] }),
      items2.length > 0 ? /* @__PURE__ */ jsx("div", { className: "grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6", children: items2.map((item) => /* @__PURE__ */ jsxs("div", { className: "relative group", children: [
        /* @__PURE__ */ jsx(ProductCard, { product: item.product }),
        /* @__PURE__ */ jsx(
          Button,
          {
            variant: "destructive",
            size: "icon",
            className: "absolute top-2 left-2",
            onClick: () => handleRemoveItem(item.product_id),
            disabled: removing2[item.product_id],
            children: /* @__PURE__ */ jsx(Trash2, { className: "h-4 w-4" })
          }
        )
      ] }, item.id)) }) : /* @__PURE__ */ jsxs("div", { className: "text-center py-12", children: [
        /* @__PURE__ */ jsx("div", { className: "inline-flex h-20 w-20 items-center justify-center rounded-full bg-muted mb-4", children: /* @__PURE__ */ jsx(Heart, { className: "h-10 w-10 text-muted-foreground" }) }),
        /* @__PURE__ */ jsx("h2", { className: "text-xl font-semibold mb-2", children: t("wishlist_empty", "Your wishlist is empty") }),
        /* @__PURE__ */ jsx("p", { className: "text-muted-foreground mb-6", children: t(
          "wishlist_empty_description",
          "Items added to your wishlist will appear here"
        ) }),
        /* @__PURE__ */ jsx(Button, { onClick: () => router.visit(route("home")), children: t("continue_shopping", "Continue Shopping") })
      ] })
    ] })
  ] });
}
const __vite_glob_0_27 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index
}, Symbol.toStringTag, { value: "Module" }));
if (typeof window !== "undefined") {
  window.axios = axios;
  window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
}
const Ziggy = { "url": "https://moda-1.turbospace.click", "port": null, "defaults": {}, "routes": { "debugbar.openhandler": { "uri": "_debugbar/open", "methods": ["GET", "HEAD"] }, "debugbar.clockwork": { "uri": "_debugbar/clockwork/{id}", "methods": ["GET", "HEAD"], "parameters": ["id"] }, "debugbar.assets.css": { "uri": "_debugbar/assets/stylesheets", "methods": ["GET", "HEAD"] }, "debugbar.assets.js": { "uri": "_debugbar/assets/javascript", "methods": ["GET", "HEAD"] }, "debugbar.cache.delete": { "uri": "_debugbar/cache/{key}/{tags?}", "methods": ["DELETE"], "parameters": ["key", "tags"] }, "debugbar.queries.explain": { "uri": "_debugbar/queries/explain", "methods": ["POST"] }, "filament.exports.download": { "uri": "filament/exports/{export}/download", "methods": ["GET", "HEAD"], "parameters": ["export"], "bindings": { "export": "id" } }, "filament.imports.failed-rows.download": { "uri": "filament/imports/{import}/failed-rows/download", "methods": ["GET", "HEAD"], "parameters": ["import"], "bindings": { "import": "id" } }, "filament.admin.auth.login": { "uri": "admin/login", "methods": ["GET", "HEAD"] }, "filament.admin.auth.password-reset.request": { "uri": "admin/password-reset/request", "methods": ["GET", "HEAD"] }, "filament.admin.auth.password-reset.reset": { "uri": "admin/password-reset/reset", "methods": ["GET", "HEAD"] }, "filament.admin.auth.logout": { "uri": "admin/logout", "methods": ["POST"] }, "filament.admin.auth.profile": { "uri": "admin/profile", "methods": ["GET", "HEAD"] }, "filament.admin.home": { "uri": "admin", "methods": ["GET", "HEAD"] }, "filament.admin.pages.customers-report": { "uri": "admin/customers-report", "methods": ["GET", "HEAD"] }, "filament.admin.pages.orders-report": { "uri": "admin/orders-report", "methods": ["GET", "HEAD"] }, "filament.admin.pages.products-report": { "uri": "admin/products-report", "methods": ["GET", "HEAD"] }, "filament.admin.resources.announcements.index": { "uri": "admin/announcements", "methods": ["GET", "HEAD"] }, "filament.admin.resources.announcements.create": { "uri": "admin/announcements/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.announcements.edit": { "uri": "admin/announcements/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.areas.index": { "uri": "admin/areas", "methods": ["GET", "HEAD"] }, "filament.admin.resources.areas.create": { "uri": "admin/areas/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.areas.view": { "uri": "admin/areas/{record}", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.areas.edit": { "uri": "admin/areas/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.brands.index": { "uri": "admin/brands", "methods": ["GET", "HEAD"] }, "filament.admin.resources.brands.create": { "uri": "admin/brands/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.brands.edit": { "uri": "admin/brands/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.categories.index": { "uri": "admin/categories", "methods": ["GET", "HEAD"] }, "filament.admin.resources.categories.create": { "uri": "admin/categories/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.categories.edit": { "uri": "admin/categories/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.direct-promotions.index": { "uri": "admin/direct-promotions", "methods": ["GET", "HEAD"] }, "filament.admin.resources.direct-promotions.create": { "uri": "admin/direct-promotions/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.direct-promotions.edit": { "uri": "admin/direct-promotions/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.govs.index": { "uri": "admin/govs", "methods": ["GET", "HEAD"] }, "filament.admin.resources.govs.create": { "uri": "admin/govs/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.govs.view": { "uri": "admin/govs/{record}", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.govs.edit": { "uri": "admin/govs/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.hero-slides.index": { "uri": "admin/hero-slides", "methods": ["GET", "HEAD"] }, "filament.admin.resources.hero-slides.create": { "uri": "admin/hero-slides/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.hero-slides.edit": { "uri": "admin/hero-slides/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.orders.index": { "uri": "admin/orders", "methods": ["GET", "HEAD"] }, "filament.admin.resources.orders.view": { "uri": "admin/orders/{record}", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.products.index": { "uri": "admin/products", "methods": ["GET", "HEAD"] }, "filament.admin.resources.products.create": { "uri": "admin/products/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.products.edit": { "uri": "admin/products/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.products.view": { "uri": "admin/products/{record}", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.promotions.index": { "uri": "admin/promotions", "methods": ["GET", "HEAD"] }, "filament.admin.resources.promotions.create": { "uri": "admin/promotions/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.promotions.edit": { "uri": "admin/promotions/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.sections.index": { "uri": "admin/sections", "methods": ["GET", "HEAD"] }, "filament.admin.resources.sections.create": { "uri": "admin/sections/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.sections.edit": { "uri": "admin/sections/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.settings.index": { "uri": "admin/settings", "methods": ["GET", "HEAD"] }, "filament.admin.resources.settings.create": { "uri": "admin/settings/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.settings.edit": { "uri": "admin/settings/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "filament.admin.resources.users.index": { "uri": "admin/users", "methods": ["GET", "HEAD"] }, "filament.admin.resources.users.create": { "uri": "admin/users/create", "methods": ["GET", "HEAD"] }, "filament.admin.resources.users.edit": { "uri": "admin/users/{record}/edit", "methods": ["GET", "HEAD"], "parameters": ["record"] }, "boost.browser-logs": { "uri": "_boost/browser-logs", "methods": ["POST"] }, "sanctum.csrf-cookie": { "uri": "sanctum/csrf-cookie", "methods": ["GET", "HEAD"] }, "livewire.update": { "uri": "livewire/update", "methods": ["POST"] }, "livewire.upload-file": { "uri": "livewire/upload-file", "methods": ["POST"] }, "livewire.preview-file": { "uri": "livewire/preview-file/{filename}", "methods": ["GET", "HEAD"], "parameters": ["filename"] }, "home": { "uri": "/", "methods": ["GET", "HEAD"] }, "products.index": { "uri": "products", "methods": ["GET", "HEAD"] }, "products.show": { "uri": "products/{product}", "methods": ["GET", "HEAD"], "parameters": ["product"], "bindings": { "product": "id" } }, "brands.index": { "uri": "brands", "methods": ["GET", "HEAD"] }, "categories.index": { "uri": "categories", "methods": ["GET", "HEAD"] }, "categories.show": { "uri": "categories/{category}", "methods": ["GET", "HEAD"], "parameters": ["category"] }, "sections.show": { "uri": "sections/{section}", "methods": ["GET", "HEAD"], "parameters": ["section"], "bindings": { "section": "id" } }, "search.results": { "uri": "search", "methods": ["GET", "HEAD"] }, "search.suggestions": { "uri": "search/suggestions", "methods": ["GET", "HEAD"] }, "pages.privacy": { "uri": "privacy", "methods": ["GET", "HEAD"] }, "pages.returns": { "uri": "returns", "methods": ["GET", "HEAD"] }, "pages.terms": { "uri": "terms", "methods": ["GET", "HEAD"] }, "pages.contact": { "uri": "contact", "methods": ["GET", "HEAD"] }, "pages.facebook-data-deletion": { "uri": "facebook-data-deletion", "methods": ["GET", "HEAD"] }, "profile.edit": { "uri": "profile", "methods": ["GET", "HEAD"] }, "profile.update": { "uri": "profile", "methods": ["PATCH"] }, "profile.destroy": { "uri": "profile", "methods": ["DELETE"] }, "cart.index": { "uri": "cart", "methods": ["GET", "HEAD"] }, "cart.add": { "uri": "cart/add", "methods": ["POST"] }, "cart.update": { "uri": "cart/{cartItem}", "methods": ["PATCH"], "parameters": ["cartItem"], "bindings": { "cartItem": "id" } }, "cart.remove": { "uri": "cart/{cartItem}", "methods": ["DELETE"], "parameters": ["cartItem"], "bindings": { "cartItem": "id" } }, "cart.clear": { "uri": "cart", "methods": ["DELETE"] }, "cart.summary": { "uri": "cart/summary", "methods": ["GET", "HEAD"] }, "wishlist.index": { "uri": "wishlist", "methods": ["GET", "HEAD"] }, "wishlist.add": { "uri": "wishlist/add", "methods": ["POST"] }, "wishlist.remove": { "uri": "wishlist/remove/{product}", "methods": ["DELETE"], "parameters": ["product"], "bindings": { "product": "id" } }, "wishlist.clear": { "uri": "wishlist", "methods": ["DELETE"] }, "wishlist.summary": { "uri": "wishlist/summary", "methods": ["GET", "HEAD"] }, "addresses.store": { "uri": "addresses", "methods": ["POST"] }, "addresses.areas": { "uri": "addresses/areas", "methods": ["GET", "HEAD"] }, "promotions.apply": { "uri": "promotions/apply", "methods": ["POST"] }, "promotions.remove": { "uri": "promotions/remove", "methods": ["DELETE"] }, "promotions.automatic": { "uri": "promotions/automatic", "methods": ["GET", "HEAD"] }, "checkout.index": { "uri": "checkout", "methods": ["GET", "HEAD"] }, "orders.store": { "uri": "orders", "methods": ["POST"] }, "orders.index": { "uri": "orders", "methods": ["GET", "HEAD"] }, "orders.show": { "uri": "orders/{order}", "methods": ["GET", "HEAD"], "wheres": { "order": "[0-9]+" }, "parameters": ["order"] }, "orders.cancel": { "uri": "orders/{order}/cancel", "methods": ["PATCH"], "wheres": { "order": "[0-9]+" }, "parameters": ["order"] }, "orders.return.request": { "uri": "orders/{order}/return", "methods": ["POST"], "wheres": { "order": "[0-9]+" }, "parameters": ["order"] }, "orders.returns.history": { "uri": "orders/returns/history", "methods": ["GET", "HEAD"] }, "payment.initiate": { "uri": "payments/initiate", "methods": ["GET", "HEAD"] }, "payment.success": { "uri": "payments/success", "methods": ["GET", "HEAD"] }, "payment.failure": { "uri": "payments/failure", "methods": ["GET", "HEAD"] }, "payment.show": { "uri": "payments/{order}", "methods": ["GET", "HEAD"], "parameters": ["order"] }, "kashier.payment.initiate": { "uri": "payments/kashier/initiate", "methods": ["GET", "HEAD"] }, "kashier.payment.success": { "uri": "payments/kashier/success", "methods": ["GET", "HEAD"] }, "kashier.payment.failure": { "uri": "payments/kashier/failure", "methods": ["GET", "HEAD"] }, "kashier.payment.show": { "uri": "payments/kashier/{order}", "methods": ["GET", "HEAD"], "parameters": ["order"] }, "payment.webhook": { "uri": "webhooks/payment", "methods": ["POST"] }, "kashier.payment.webhook": { "uri": "webhooks/kashier", "methods": ["POST"] }, "register": { "uri": "register", "methods": ["GET", "HEAD"] }, "login": { "uri": "login", "methods": ["GET", "HEAD"] }, "social.login": { "uri": "auth/{provider}", "methods": ["GET", "HEAD"], "parameters": ["provider"] }, "social.callback": { "uri": "auth/{provider}/callback", "methods": ["GET", "HEAD"], "parameters": ["provider"] }, "password.request": { "uri": "forgot-password", "methods": ["GET", "HEAD"] }, "password.email": { "uri": "forgot-password", "methods": ["POST"] }, "password.reset": { "uri": "reset-password/{token}", "methods": ["GET", "HEAD"], "parameters": ["token"] }, "password.store": { "uri": "reset-password", "methods": ["POST"] }, "verification.notice": { "uri": "verify-email", "methods": ["GET", "HEAD"] }, "verification.verify": { "uri": "verify-email/{id}/{hash}", "methods": ["GET", "HEAD"], "parameters": ["id", "hash"] }, "verification.send": { "uri": "email/verification-notification", "methods": ["POST"] }, "password.confirm": { "uri": "confirm-password", "methods": ["GET", "HEAD"] }, "password.update": { "uri": "password", "methods": ["PUT"] }, "logout": { "uri": "logout", "methods": ["POST"] } } };
if (typeof window !== "undefined" && typeof window.Ziggy !== "undefined") {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
function createRouteFunction(config) {
  return function route2(name2, params) {
    const routeConfig = config.routes[name2];
    if (!routeConfig) {
      console.warn(`Route "${name2}" not found`);
      return "/";
    }
    let uri = routeConfig.uri;
    if (params && routeConfig.parameters) {
      routeConfig.parameters.forEach((param) => {
        const value = params[param];
        if (value !== void 0) {
          uri = uri.replace(`{${param}}`, value);
        }
      });
    }
    uri = uri.replace(/\/\{[^}]*\?\}/g, "");
    return `${config.url}${uri === "/" ? "" : "/"}${uri}`;
  };
}
const routeFn = createRouteFunction(Ziggy);
if (typeof globalThis !== "undefined") {
  globalThis.route = routeFn;
}
if (typeof window !== "undefined") {
  window.route = routeFn;
}
const ToastProvider = ToastPrimitives.Provider;
const ToastViewport = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  ToastPrimitives.Viewport,
  {
    ref,
    className: cn(
      "fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]",
      className
    ),
    ...props
  }
));
ToastViewport.displayName = ToastPrimitives.Viewport.displayName;
const toastVariants = cva(
  "group pointer-events-auto relative flex w-full items-center justify-between space-x-2 overflow-hidden rounded-md border p-4 pr-6 shadow-lg transition-all data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full",
  {
    variants: {
      variant: {
        default: "border bg-background text-foreground",
        destructive: "destructive group border-destructive bg-destructive text-destructive-foreground"
      }
    },
    defaultVariants: {
      variant: "default"
    }
  }
);
const Toast = React.forwardRef(({ className, variant: variant2, ...props }, ref) => {
  return /* @__PURE__ */ jsx(
    ToastPrimitives.Root,
    {
      ref,
      className: cn(toastVariants({ variant: variant2 }), className),
      ...props
    }
  );
});
Toast.displayName = ToastPrimitives.Root.displayName;
const ToastAction = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  ToastPrimitives.Action,
  {
    ref,
    className: cn(
      "inline-flex h-8 shrink-0 items-center justify-center rounded-md border bg-transparent px-3 text-sm font-medium transition-colors hover:bg-secondary focus:outline-none focus:ring-1 focus:ring-ring disabled:pointer-events-none disabled:opacity-50 group-[.destructive]:border-muted/40 group-[.destructive]:hover:border-destructive/30 group-[.destructive]:hover:bg-destructive group-[.destructive]:hover:text-destructive-foreground group-[.destructive]:focus:ring-destructive",
      className
    ),
    ...props
  }
));
ToastAction.displayName = ToastPrimitives.Action.displayName;
const ToastClose = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  ToastPrimitives.Close,
  {
    ref,
    className: cn(
      "absolute right-1 top-1 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-1 group-hover:opacity-100 group-[.destructive]:text-red-300 group-[.destructive]:hover:text-red-50 group-[.destructive]:focus:ring-red-400 group-[.destructive]:focus:ring-offset-red-600",
      className
    ),
    "toast-close": "",
    ...props,
    children: /* @__PURE__ */ jsx(X, { className: "h-4 w-4" })
  }
));
ToastClose.displayName = ToastPrimitives.Close.displayName;
const ToastTitle = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  ToastPrimitives.Title,
  {
    ref,
    className: cn("text-sm font-semibold [&+div]:text-xs", className),
    ...props
  }
));
ToastTitle.displayName = ToastPrimitives.Title.displayName;
const ToastDescription = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  ToastPrimitives.Description,
  {
    ref,
    className: cn("text-sm opacity-90", className),
    ...props
  }
));
ToastDescription.displayName = ToastPrimitives.Description.displayName;
function Toaster() {
  const { toasts } = useToast();
  return /* @__PURE__ */ jsxs(ToastProvider, { children: [
    toasts.map(function({ id, title, description: description2, action, ...props }) {
      return /* @__PURE__ */ jsxs(Toast, { ...props, children: [
        /* @__PURE__ */ jsxs("div", { className: "grid gap-1", children: [
          title && /* @__PURE__ */ jsx(ToastTitle, { children: title }),
          description2 && /* @__PURE__ */ jsx(ToastDescription, { children: description2 })
        ] }),
        action,
        /* @__PURE__ */ jsx(ToastClose, {})
      ] }, id);
    }),
    /* @__PURE__ */ jsx(ToastViewport, {})
  ] });
}
function SiteLogo({ className = "", size: size2 = "md", showTitle = true }) {
  const { title, logo } = useSiteBranding();
  const sizeClasses = {
    sm: "h-8 w-auto",
    md: "h-12 w-auto",
    lg: "h-16 w-auto",
    xl: "h-24 w-auto"
  };
  const resolvedLogo = resolveStoragePath(logo);
  if (resolvedLogo) {
    return /* @__PURE__ */ jsxs("div", { className: `flex items-center gap-3 ${className}`, children: [
      /* @__PURE__ */ jsx(
        "img",
        {
          src: resolvedLogo,
          alt: title,
          className: sizeClasses[size2] + " rounded-xl"
        }
      ),
      showTitle && /* @__PURE__ */ jsx("span", { className: "font-bold text-lg", children: title })
    ] });
  }
  return /* @__PURE__ */ jsx("div", { className: `flex items-center ${className}`, children: /* @__PURE__ */ jsx("h1", { className: "font-bold text-xl", children: title }) });
}
function SocialLinks({ className = "", iconSize = 24, showLabels = false }) {
  const settings = useSettings();
  const socialLinks = JSON.parse(settings.social_links || "{}");
  const socialPlatforms = [
    { key: "facebook", label: "Facebook", icon: Facebook },
    { key: "twitter", label: "X (Twitter)", icon: X },
    { key: "instagram", label: "Instagram", icon: Instagram },
    { key: "linkedin", label: "LinkedIn", icon: Linkedin },
    { key: "youtube", label: "YouTube", icon: Youtube },
    { key: "tiktok", label: "TikTok", icon: Music2 }
  ];
  const activePlatforms = socialPlatforms.filter(
    (platform) => socialLinks[platform.key] && socialLinks[platform.key].trim() !== ""
  );
  if (activePlatforms.length === 0) {
    return null;
  }
  return /* @__PURE__ */ jsx("div", { className: `flex gap-3 ${className}`, children: activePlatforms.map((platform) => {
    const IconComponent = platform.icon;
    return /* @__PURE__ */ jsx(
      "a",
      {
        href: socialLinks[platform.key],
        target: "_blank",
        rel: "noopener noreferrer",
        className: "text-muted-foreground hover:text-foreground transition-colors",
        children: showLabels ? /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-2", children: [
          /* @__PURE__ */ jsx(IconComponent, { size: iconSize }),
          platform.label
        ] }) : /* @__PURE__ */ jsx(IconComponent, { size: iconSize })
      },
      platform.key
    );
  }) });
}
function ContactInfo({ className = "" }) {
  const settings = useSettings();
  if (!settings.contact_email) {
    return null;
  }
  return /* @__PURE__ */ jsx("div", { className, children: /* @__PURE__ */ jsx(
    "a",
    {
      href: `mailto:${settings.contact_email}`,
      className: "text-muted-foreground hover:text-foreground transition-colors",
      children: settings.contact_email
    }
  ) });
}
function MaintenanceWrapper({ children }) {
  const settings = useSettings();
  if (settings.maintenance_mode) {
    return /* @__PURE__ */ jsx("div", { className: "min-h-screen flex items-center justify-center bg-muted", children: /* @__PURE__ */ jsxs("div", { className: "max-w-md mx-auto text-center p-6", children: [
      /* @__PURE__ */ jsx(SiteLogo, { className: "justify-center mb-6" }),
      /* @__PURE__ */ jsx("h1", { className: "text-2xl font-bold mb-4", children: "Under Maintenance" }),
      /* @__PURE__ */ jsx("p", { className: "text-muted-foreground mb-6", children: "We're currently performing scheduled maintenance. Please check back later." }),
      /* @__PURE__ */ jsx(ContactInfo, {})
    ] }) });
  }
  return /* @__PURE__ */ jsx(Fragment, { children });
}
function ApplicationLogo({ className, showTitle = false }) {
  return /* @__PURE__ */ jsx(
    SiteLogo,
    {
      className,
      size: "md",
      showTitle
    }
  );
}
function SearchBar({ isOpen, onClose }) {
  const { t, currentLocale } = useI18n();
  const searchRef = useRef(null);
  const searchInputRef = useRef(null);
  const [query, setQuery] = useState("");
  const [showSuggestions, setShowSuggestions] = useState(false);
  useEffect(() => {
    if (isOpen) {
      setTimeout(() => {
        var _a;
        (_a = searchInputRef.current) == null ? void 0 : _a.focus();
      }, 100);
    } else {
      setQuery("");
      setShowSuggestions(false);
    }
  }, [isOpen]);
  useEffect(() => {
    const handlePopState = (event) => {
      if (isOpen) {
        event.preventDefault();
        onClose();
      }
    };
    const handleEscapeKey = (event) => {
      if (event.key === "Escape" && isOpen) {
        onClose();
      }
    };
    if (isOpen) {
      window.history.pushState({ searchModalOpen: true }, "");
      window.addEventListener("popstate", handlePopState);
      document.addEventListener("keydown", handleEscapeKey);
    }
    return () => {
      window.removeEventListener("popstate", handlePopState);
      document.removeEventListener("keydown", handleEscapeKey);
    };
  }, [isOpen, onClose]);
  useEffect(() => {
    const handleClickOutside = (event) => {
      const target = event.target;
      if (target.classList.contains("search-backdrop")) {
        onClose();
      }
    };
    if (isOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    }
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [isOpen, onClose]);
  useEffect(() => {
    if (query.length >= 2) {
      setShowSuggestions(true);
    } else {
      setShowSuggestions(false);
    }
  }, [query]);
  const handleSearch = (e) => {
    e.preventDefault();
    if (query.trim()) {
      router.get("/search", { q: query.trim() });
      onClose();
    }
  };
  const closeSuggestions = () => {
    setShowSuggestions(false);
    onClose();
  };
  return /* @__PURE__ */ jsx(
    "div",
    {
      className: `fixed top-0 left-0 w-full h-screen bg-background/95 z-50 transition-all duration-300 search-backdrop ${isOpen ? "opacity-100 visible" : "opacity-0 invisible"}`,
      children: /* @__PURE__ */ jsx("div", { className: "container mx-auto pt-20 px-4", children: /* @__PURE__ */ jsxs(
        "div",
        {
          ref: searchRef,
          className: "w-full max-w-3xl mx-auto",
          onClick: (e) => e.stopPropagation(),
          children: [
            /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-6", children: [
              /* @__PURE__ */ jsx("h2", { className: "text-2xl font-bold", children: t("search_products") }),
              /* @__PURE__ */ jsx(
                Button,
                {
                  variant: "ghost",
                  size: "icon",
                  onClick: onClose,
                  className: "rounded-full",
                  "aria-label": t("common.close"),
                  children: /* @__PURE__ */ jsx(X, { className: "h-5 w-5" })
                }
              )
            ] }),
            /* @__PURE__ */ jsx("form", { onSubmit: handleSearch, className: "relative", children: /* @__PURE__ */ jsxs("div", { className: "relative rounded-xl overflow-hidden shadow-md focus-within:shadow-lg transition-shadow duration-300", children: [
              /* @__PURE__ */ jsx("div", { className: "absolute ltr:left-4 rtl:right-4 top-1/2 transform -translate-y-1/2 text-muted-foreground h-5 w-5", children: /* @__PURE__ */ jsx(Search, { className: "h-5 w-5" }) }),
              /* @__PURE__ */ jsx(
                Input,
                {
                  ref: searchInputRef,
                  type: "text",
                  placeholder: t("search_placeholder"),
                  value: query,
                  onChange: (e) => setQuery(e.target.value),
                  className: cn(
                    "border-2 focus:border-primary/50 pl-12 pr-12 text-lg h-16 rounded-xl",
                    "focus-visible:ring-0 focus-visible:ring-offset-0",
                    currentLocale === "ar" ? "pr-12 pl-16" : "pl-12 pr-16"
                  )
                }
              ),
              /* @__PURE__ */ jsxs("div", { className: "absolute ltr:right-4 rtl:left-4 top-1/2 transform -translate-y-1/2 w-fit", children: [
                query && /* @__PURE__ */ jsx(
                  Button,
                  {
                    type: "button",
                    size: "icon",
                    variant: "ghost",
                    onClick: () => setQuery(""),
                    className: "mr-1 h-8 w-8 rounded-full hover:bg-muted",
                    "aria-label": t("clear"),
                    children: /* @__PURE__ */ jsx(X, { className: "h-4 w-4" })
                  }
                ),
                /* @__PURE__ */ jsx(
                  Button,
                  {
                    type: "submit",
                    size: "sm",
                    variant: "default",
                    className: "rounded-full h-8 w-8 p-0",
                    "aria-label": t("search"),
                    children: /* @__PURE__ */ jsx(Search, { className: "h-4 w-4" })
                  }
                )
              ] })
            ] }) }),
            /* @__PURE__ */ jsx("div", { className: "relative", children: /* @__PURE__ */ jsx(
              SearchSuggestions,
              {
                query,
                isOpen: showSuggestions,
                onClose: closeSuggestions,
                className: "mt-6"
              }
            ) })
          ]
        }
      ) })
    }
  );
}
function Footer() {
  const { t } = useI18n();
  const settings = useSettings();
  const showPrivacyPolicy = settings.show_privacy_policy !== false;
  const showReturnPolicy = settings.show_return_policy !== false;
  const showTermsOfService = settings.show_terms_of_service !== false;
  const hasPolicyLinks = showPrivacyPolicy || showReturnPolicy || showTermsOfService;
  return /* @__PURE__ */ jsx("footer", { className: "bg-muted/50 border-t mt-auto mb-12 lg:mb-0", children: /* @__PURE__ */ jsxs("div", { className: "container px-4 py-8", children: [
    /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
        /* @__PURE__ */ jsx(SiteLogo, { showTitle: true }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-muted-foreground", children: t("footer_description", "Your trusted online shopping destination") }),
        /* @__PURE__ */ jsx(ContactInfo, {})
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
        /* @__PURE__ */ jsx("h3", { className: "font-semibold", children: t("quick_links", "Quick Links") }),
        /* @__PURE__ */ jsxs("ul", { className: "space-y-2 text-sm", children: [
          /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", { href: "/categories", className: "text-muted-foreground hover:text-foreground transition-colors", children: t("categories", "Categories") }) }),
          /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", { href: "/brands", className: "text-muted-foreground hover:text-foreground transition-colors", children: t("brands", "Brands") }) }),
          /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", { href: "/orders", className: "text-muted-foreground hover:text-foreground transition-colors", children: t("my_orders", "My Orders") }) }),
          /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", { href: "/wishlist", className: "text-muted-foreground hover:text-foreground transition-colors", children: t("wishlist", "Wishlist") }) })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
        /* @__PURE__ */ jsx("h3", { className: "font-semibold", children: t("follow_us", "Follow Us") }),
        /* @__PURE__ */ jsx(SocialLinks, { showLabels: true, className: "flex-col items-start" })
      ] })
    ] }),
    /* @__PURE__ */ jsx("hr", { className: "my-6" }),
    /* @__PURE__ */ jsxs("div", { className: "flex flex-col md:flex-row justify-between items-center text-sm text-muted-foreground", children: [
      /* @__PURE__ */ jsxs("p", { children: [
        "© ",
        (/* @__PURE__ */ new Date()).getFullYear(),
        " ",
        t("all_rights_reserved", "All rights reserved")
      ] }),
      hasPolicyLinks && /* @__PURE__ */ jsxs("div", { className: "flex gap-4 mt-4 md:mt-0", children: [
        showPrivacyPolicy && /* @__PURE__ */ jsx(Link, { href: "/privacy", className: "hover:text-foreground transition-colors", children: t("privacy_policy", "Privacy Policy") }),
        showReturnPolicy && /* @__PURE__ */ jsx(Link, { href: "/returns", className: "hover:text-foreground transition-colors", children: t("return_policy", "Return Policy") }),
        showTermsOfService && /* @__PURE__ */ jsx(Link, { href: "/terms", className: "hover:text-foreground transition-colors", children: t("terms_of_service", "Terms of Service") })
      ] })
    ] })
  ] }) });
}
const useIsomorphicLayoutEffect = typeof window !== "undefined" ? useLayoutEffect : useEffect;
const NavigationMenu = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  NavigationMenuPrimitive.Root,
  {
    ref,
    className: cn(
      "relative z-10 flex max-w-max flex-1 items-center justify-center",
      className
    ),
    ...props,
    children: [
      children,
      /* @__PURE__ */ jsx(NavigationMenuViewport, {})
    ]
  }
));
NavigationMenu.displayName = NavigationMenuPrimitive.Root.displayName;
const NavigationMenuList = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  NavigationMenuPrimitive.List,
  {
    ref,
    className: cn(
      "group flex flex-1 list-none items-center justify-center space-x-1",
      className
    ),
    ...props
  }
));
NavigationMenuList.displayName = NavigationMenuPrimitive.List.displayName;
const NavigationMenuItem = NavigationMenuPrimitive.Item;
const navigationMenuTriggerStyle = cva(
  "group inline-flex h-9 w-max items-center justify-center rounded-md bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50 data-[state=open]:text-accent-foreground data-[state=open]:bg-accent/50 data-[state=open]:hover:bg-accent data-[state=open]:focus:bg-accent"
);
const NavigationMenuTrigger = React.forwardRef(({ className, children, ...props }, ref) => /* @__PURE__ */ jsxs(
  NavigationMenuPrimitive.Trigger,
  {
    ref,
    className: cn(navigationMenuTriggerStyle(), "group", className),
    ...props,
    children: [
      children,
      " ",
      /* @__PURE__ */ jsx(
        ChevronDown,
        {
          className: "relative top-[1px] ml-1 h-3 w-3 transition duration-300 group-data-[state=open]:rotate-180",
          "aria-hidden": "true"
        }
      )
    ]
  }
));
NavigationMenuTrigger.displayName = NavigationMenuPrimitive.Trigger.displayName;
const NavigationMenuContent = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  NavigationMenuPrimitive.Content,
  {
    ref,
    className: cn(
      "left-0 top-0 w-full data-[motion^=from-]:animate-in data-[motion^=to-]:animate-out data-[motion^=from-]:fade-in data-[motion^=to-]:fade-out data-[motion=from-end]:slide-in-from-right-52 data-[motion=from-start]:slide-in-from-left-52 data-[motion=to-end]:slide-out-to-right-52 data-[motion=to-start]:slide-out-to-left-52 md:absolute md:w-auto ",
      className
    ),
    ...props
  }
));
NavigationMenuContent.displayName = NavigationMenuPrimitive.Content.displayName;
const NavigationMenuLink = NavigationMenuPrimitive.Link;
const NavigationMenuViewport = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx("div", { className: cn("absolute left-0 top-full flex justify-center"), children: /* @__PURE__ */ jsx(
  NavigationMenuPrimitive.Viewport,
  {
    className: cn(
      "origin-top-center relative mt-1.5 h-[var(--radix-navigation-menu-viewport-height)] w-full overflow-hidden rounded-md border bg-popover text-popover-foreground shadow data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-90 md:w-[var(--radix-navigation-menu-viewport-width)]",
      className
    ),
    ref,
    ...props
  }
) }));
NavigationMenuViewport.displayName = NavigationMenuPrimitive.Viewport.displayName;
const NavigationMenuIndicator = React.forwardRef(({ className, ...props }, ref) => /* @__PURE__ */ jsx(
  NavigationMenuPrimitive.Indicator,
  {
    ref,
    className: cn(
      "top-full z-[1] flex h-1.5 items-end justify-center overflow-hidden data-[state=visible]:animate-in data-[state=hidden]:animate-out data-[state=hidden]:fade-out data-[state=visible]:fade-in",
      className
    ),
    ...props,
    children: /* @__PURE__ */ jsx("div", { className: "relative top-[60%] h-2 w-2 rotate-45 rounded-tl-sm bg-border shadow-md" })
  }
));
NavigationMenuIndicator.displayName = NavigationMenuPrimitive.Indicator.displayName;
function DesktopNav({ brands: brands2, categories: categories2 }) {
  const { t, getLocalizedField, direction } = useI18n();
  const settings = useSettings();
  const showContactPage = settings.show_contact_page !== false;
  return /* @__PURE__ */ jsx("div", { className: "hidden flex-1 lg:flex", children: /* @__PURE__ */ jsx(NavigationMenu, { dir: direction, children: /* @__PURE__ */ jsxs(NavigationMenuList, { dir: direction, children: [
    /* @__PURE__ */ jsx(NavigationMenuItem, { className: "rtl:mx-1", children: /* @__PURE__ */ jsx(Link, { href: "/", className: navigationMenuTriggerStyle(), children: t("home", "Home") }) }),
    /* @__PURE__ */ jsxs(NavigationMenuItem, { children: [
      /* @__PURE__ */ jsx(NavigationMenuTrigger, { children: t("brands", "Brands") }),
      /* @__PURE__ */ jsx(NavigationMenuContent, { children: brands2 && brands2.length > 0 ? /* @__PURE__ */ jsx("ul", { className: "grid w-[400px] gap-3 p-4 md:w-[500px] md:grid-cols-2 lg:w-[600px]", children: brands2.map((brand) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx(NavigationMenuLink, { asChild: true, children: /* @__PURE__ */ jsxs(
        Link,
        {
          href: `/search?brands[]=${brand.id}`,
          className: "flex items-center gap-2 select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground",
          children: [
            /* @__PURE__ */ jsx(
              Image,
              {
                src: brand.image_url,
                alt: getLocalizedField(
                  brand,
                  "name"
                ),
                className: "rounded-md w-[30px] h-[30px] object-contain object-center"
              }
            ),
            /* @__PURE__ */ jsx("div", { className: "text-sm font-medium leading-none", children: getLocalizedField(
              brand,
              "name"
            ) })
          ]
        }
      ) }) }, brand.id)) }) : /* @__PURE__ */ jsx("div", { className: "w-[300px] p-6", children: /* @__PURE__ */ jsx(
        EmptyState,
        {
          icon: /* @__PURE__ */ jsx(ShoppingBag, {}),
          title: t(
            "no_brands_available",
            "No brands available"
          )
        }
      ) }) })
    ] }),
    /* @__PURE__ */ jsxs(NavigationMenuItem, { children: [
      /* @__PURE__ */ jsx(NavigationMenuTrigger, { children: t("categories", "Categories") }),
      /* @__PURE__ */ jsx(NavigationMenuContent, { children: categories2 && categories2.length > 0 ? /* @__PURE__ */ jsx("ul", { className: "grid w-[400px] gap-3 p-4 md:w-[500px] md:grid-cols-2 lg:w-[600px]", children: categories2.map((category2) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx(NavigationMenuLink, { asChild: true, children: /* @__PURE__ */ jsx(
        Link,
        {
          href: `/search?categories[]=${category2.id}`,
          className: "block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground",
          children: /* @__PURE__ */ jsx("div", { className: "text-sm font-medium leading-none", children: getLocalizedField(
            category2,
            "name"
          ) })
        }
      ) }) }, category2.id)) }) : /* @__PURE__ */ jsx("div", { className: "w-[300px] p-6", children: /* @__PURE__ */ jsx(
        EmptyState,
        {
          icon: /* @__PURE__ */ jsx(FolderX, { size: 32 }),
          title: t(
            "no_categories_available",
            "No categories available"
          )
        }
      ) }) })
    ] }),
    /* @__PURE__ */ jsx(NavigationMenuItem, { children: /* @__PURE__ */ jsx(
      Link,
      {
        href: "/sections/4",
        className: navigationMenuTriggerStyle(),
        children: t("best_sellers", "Best Sellers")
      }
    ) }),
    showContactPage && /* @__PURE__ */ jsx(NavigationMenuItem, { children: /* @__PURE__ */ jsx(
      Link,
      {
        href: "/contact",
        className: navigationMenuTriggerStyle(),
        children: t("contact", "Contact")
      }
    ) })
  ] }) }) });
}
function MobileBottomNav({ cartItemsCount = 0, onSearchClick }) {
  const { t } = useI18n();
  return /* @__PURE__ */ jsx("div", { className: "fixed bottom-0 left-0 right-0 z-40 block border-t border-border bg-background lg:hidden", children: /* @__PURE__ */ jsxs("div", { className: "container flex h-14 items-center justify-between px-4", children: [
    /* @__PURE__ */ jsx(Button, { variant: "ghost", size: "icon", asChild: true, children: /* @__PURE__ */ jsx(Link, { href: "/", "aria-label": t("home", "Home"), children: /* @__PURE__ */ jsx(Home$2, { className: "h-5 w-5" }) }) }),
    /* @__PURE__ */ jsx(
      Button,
      {
        variant: "ghost",
        size: "icon",
        onClick: onSearchClick,
        "aria-label": t("search", "Search"),
        children: /* @__PURE__ */ jsx(Search, { className: "h-5 w-5" })
      }
    ),
    /* @__PURE__ */ jsx(Button, { variant: "ghost", size: "icon", asChild: true, "aria-label": t("wishlist", "Wishlist"), children: /* @__PURE__ */ jsx(Link, { href: "/wishlist", children: /* @__PURE__ */ jsx(Heart, { className: "h-5 w-5" }) }) }),
    /* @__PURE__ */ jsx(Button, { variant: "ghost", size: "icon", asChild: true, "aria-label": t("cart", "Cart"), children: /* @__PURE__ */ jsx(Link, { href: "/cart", children: /* @__PURE__ */ jsxs("div", { className: "relative", children: [
      /* @__PURE__ */ jsx(ShoppingCart, { className: "h-5 w-5" }),
      cartItemsCount > 0 && /* @__PURE__ */ jsx("span", { className: "absolute ltr:-right-2 rtl:-left-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] text-primary-foreground", children: cartItemsCount > 9 ? "9+" : cartItemsCount })
    ] }) }) })
  ] }) });
}
function MobileNav({ brands: brands2, categories: categories2 }) {
  const { direction, t, getLocalizedField } = useI18n();
  const settings = useSettings();
  const isRtl = direction === "rtl";
  const showContactPage = settings.show_contact_page !== false;
  return /* @__PURE__ */ jsxs(Sheet, { children: [
    /* @__PURE__ */ jsx(SheetTrigger, { asChild: true, children: /* @__PURE__ */ jsx(
      Button,
      {
        variant: "ghost",
        size: "icon",
        className: "lg:hidden",
        "aria-label": "Menu",
        children: /* @__PURE__ */ jsx(Menu, { className: "h-5 w-5" })
      }
    ) }),
    /* @__PURE__ */ jsx(
      SheetContent,
      {
        side: isRtl ? "left" : "right",
        className: "w-[300px]",
        children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-6 py-6 h-full overflow-y-auto", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
            /* @__PURE__ */ jsx(Link, { href: "/", className: "flex items-center", children: /* @__PURE__ */ jsx(ApplicationLogo, { className: "h-8 w-auto  rounded-xl" }) }),
            /* @__PURE__ */ jsx(LanguageSwitcher, {})
          ] }),
          /* @__PURE__ */ jsxs("nav", { className: "flex flex-col space-y-4", children: [
            /* @__PURE__ */ jsx(SheetClose, { asChild: true, children: /* @__PURE__ */ jsx(Link, { href: "/", className: "text-lg font-medium", children: t("home", "Home") }) }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
              /* @__PURE__ */ jsx("h3", { className: "font-medium text-muted-foreground", children: t("brands", "Brands") }),
              brands2 && brands2.length > 0 ? /* @__PURE__ */ jsx("div", { className: "ltr:ml-4 rtl:mr-4 flex flex-col space-y-2", children: brands2.map((brand) => /* @__PURE__ */ jsx(SheetClose, { asChild: true, children: /* @__PURE__ */ jsx(
                Link,
                {
                  href: `/search?brands[]=${brand.id}`,
                  className: "text-sm hover:text-primary",
                  children: getLocalizedField(brand, "name")
                }
              ) }, brand.id)) }) : /* @__PURE__ */ jsx("div", { className: "ltr:ml-4 rtl:mr-4", children: /* @__PURE__ */ jsx(
                EmptyState,
                {
                  icon: /* @__PURE__ */ jsx(ShoppingBag, { size: 24 }),
                  title: t(
                    "no_brands_available",
                    "No brands available"
                  )
                }
              ) })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
              /* @__PURE__ */ jsx("h3", { className: "font-medium text-muted-foreground", children: t("categories", "Categories") }),
              categories2 && categories2.length > 0 ? /* @__PURE__ */ jsx("div", { className: "ltr:ml-4 rtl:mr-4 flex flex-col space-y-2", children: categories2.map((category2) => /* @__PURE__ */ jsx(SheetClose, { asChild: true, children: /* @__PURE__ */ jsx(
                Link,
                {
                  href: `/search?categories[]=${category2.id}`,
                  className: "text-sm hover:text-primary",
                  children: getLocalizedField(
                    category2,
                    "name"
                  )
                }
              ) }, category2.id)) }) : /* @__PURE__ */ jsx("div", { className: "ltr:ml-4 rtl:mr-4", children: /* @__PURE__ */ jsx(
                EmptyState,
                {
                  icon: /* @__PURE__ */ jsx(FolderX, { size: 24 }),
                  title: t(
                    "no_categories_available",
                    "No categories available"
                  )
                }
              ) })
            ] }),
            /* @__PURE__ */ jsx(SheetClose, { asChild: true, children: /* @__PURE__ */ jsx(
              Link,
              {
                href: "/sections/4",
                className: "text-lg font-medium",
                children: t("best_sellers", "Best Sellers")
              }
            ) }),
            showContactPage && /* @__PURE__ */ jsx(SheetClose, { asChild: true, children: /* @__PURE__ */ jsx(Link, { href: "/contact", className: "text-lg font-medium", children: t("contact", "Contact") }) })
          ] })
        ] })
      }
    )
  ] });
}
function UserActions({ user, cartItemsCount = 0, onSearchClick }) {
  var _a;
  const { t } = useI18n();
  return /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 ltr:ml-auto rtl:mr-auto", children: [
    /* @__PURE__ */ jsx(LanguageSwitcher, { className: "hidden sm:flex" }),
    /* @__PURE__ */ jsx(
      Button,
      {
        variant: "ghost",
        size: "icon",
        onClick: onSearchClick,
        "aria-label": t("search", "Search"),
        className: "hidden sm:flex",
        children: /* @__PURE__ */ jsx(Search, { className: "h-5 w-5" })
      }
    ),
    /* @__PURE__ */ jsx(
      Button,
      {
        variant: "ghost",
        size: "icon",
        asChild: true,
        "aria-label": t("wishlist", "Wishlist"),
        children: /* @__PURE__ */ jsx(Link, { href: "/wishlist", children: /* @__PURE__ */ jsx(Heart, { className: "h-5 w-5" }) })
      }
    ),
    user ? /* @__PURE__ */ jsxs(DropdownMenu, { children: [
      /* @__PURE__ */ jsx(DropdownMenuTrigger, { asChild: true, children: /* @__PURE__ */ jsx(Button, { variant: "ghost", size: "icon", className: "rounded-full", children: /* @__PURE__ */ jsxs(Avatar, { className: "h-8 w-8", children: [
        /* @__PURE__ */ jsx(AvatarImage, { src: user.avatar || "", alt: user.name }),
        /* @__PURE__ */ jsx(AvatarFallback, { children: ((_a = user.name) == null ? void 0 : _a.charAt(0)) || "U" })
      ] }) }) }),
      /* @__PURE__ */ jsxs(DropdownMenuContent, { align: "end", children: [
        /* @__PURE__ */ jsx(DropdownMenuItem, { asChild: true, children: /* @__PURE__ */ jsx(Link, { href: route("profile.edit"), children: t("profile", "Profile") }) }),
        /* @__PURE__ */ jsx(DropdownMenuItem, { asChild: true, children: /* @__PURE__ */ jsx(Link, { href: "/orders", children: t("my_orders", "My Orders") }) }),
        /* @__PURE__ */ jsx(DropdownMenuItem, { asChild: true, children: /* @__PURE__ */ jsx(Link, { href: route("logout"), method: "post", as: "button", className: "w-full text-left", children: t("log_out", "Logout") }) })
      ] })
    ] }) : /* @__PURE__ */ jsx(Button, { variant: "ghost", size: "icon", asChild: true, children: /* @__PURE__ */ jsx(Link, { href: route("login"), children: /* @__PURE__ */ jsx(User, { className: "h-5 w-5" }) }) }),
    /* @__PURE__ */ jsx(
      Button,
      {
        variant: "ghost",
        size: "icon",
        asChild: true,
        "aria-label": t("cart", "Cart"),
        className: "hidden sm:flex",
        children: /* @__PURE__ */ jsx(Link, { href: "/cart", children: /* @__PURE__ */ jsxs("div", { className: "relative", children: [
          /* @__PURE__ */ jsx(ShoppingCart, { className: "h-5 w-5" }),
          cartItemsCount > 0 && /* @__PURE__ */ jsx("span", { className: "absolute flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] text-primary-foreground ltr:-right-2 rtl:-left-2 -top-2", children: cartItemsCount > 9 ? "9+" : cartItemsCount })
        ] }) })
      }
    )
  ] });
}
function MainLayout({
  header,
  children
}) {
  const {
    auth,
    categories: categories2,
    brands: brands2,
    cartInfo: cart2
  } = usePage().props;
  const { direction, t, getLocalizedField } = useI18n();
  const user = auth == null ? void 0 : auth.user;
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const cartItemsCount = cart2.itemsCount;
  const handleSearchClick = () => {
    setIsSearchOpen(!isSearchOpen);
  };
  const section = useRef(null);
  const animationInjection = useRef(null);
  useIsomorphicLayoutEffect(() => {
    const logo = document.querySelector(
      ".loading-container"
    );
    logo == null ? void 0 : logo.classList.add("disabled");
  }, []);
  useIsomorphicLayoutEffect(() => {
    const existingAnimation = document.getElementById(
      "section-logo-animation"
    );
    router.on("start", (e) => {
      var _a, _b;
      if (e.detail.visit.method !== "get" || e.detail.visit.url.pathname === window.location.pathname || e.detail.visit.only.length !== 0)
        return;
      (_a = section.current) == null ? void 0 : _a.classList.remove("section-loaded");
      (_b = section.current) == null ? void 0 : _b.classList.add("section-go-away");
      if (animationInjection.current && existingAnimation) {
        animationInjection.current.appendChild(existingAnimation);
        animationInjection.current.classList.remove("hidden");
        animationInjection.current.classList.add("block");
      }
    });
    router.on("finish", (e) => {
      var _a, _b;
      if (e.detail.visit.method !== "get" || e.detail.visit.only.length !== 0)
        return;
      (_a = section.current) == null ? void 0 : _a.classList.remove("section-go-away");
      (_b = section.current) == null ? void 0 : _b.classList.add("section-loaded");
      if (animationInjection.current && existingAnimation) {
        animationInjection.current.classList.remove("block");
        animationInjection.current.classList.add("hidden");
      }
    });
    window.addEventListener("popstate", () => {
      setTimeout(
        () => window.scrollTo({
          top: window.history.state.documentScrollPosition.top,
          behavior: "smooth"
        }),
        100
      );
    });
  }, []);
  return /* @__PURE__ */ jsx(MaintenanceWrapper, { children: /* @__PURE__ */ jsxs(
    "div",
    {
      className: "flex min-h-screen flex-col bg-background",
      dir: direction,
      children: [
        /* @__PURE__ */ jsxs("nav", { className: "sticky top-0 z-40 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60", children: [
          /* @__PURE__ */ jsxs("div", { className: "container flex h-16 gap-4 items-center px-4", children: [
            /* @__PURE__ */ jsx("div", { className: "flex items-center ltr:mr-4 rtl:ml-4", children: /* @__PURE__ */ jsx(Link, { href: "/", children: /* @__PURE__ */ jsx(ApplicationLogo, { className: "h-8 w-auto  rounded-xl" }) }) }),
            /* @__PURE__ */ jsx(DesktopNav, { brands: brands2, categories: categories2 }),
            /* @__PURE__ */ jsx(
              UserActions,
              {
                user,
                cartItemsCount,
                onSearchClick: handleSearchClick
              }
            ),
            /* @__PURE__ */ jsx(MobileNav, { brands: brands2, categories: categories2 })
          ] }),
          /* @__PURE__ */ jsx(
            SearchBar,
            {
              isOpen: isSearchOpen,
              onClose: () => setIsSearchOpen(false)
            }
          )
        ] }),
        /* @__PURE__ */ jsx(
          MobileBottomNav,
          {
            cartItemsCount,
            onSearchClick: handleSearchClick
          }
        ),
        header && /* @__PURE__ */ jsx("header", { className: "bg-white shadow", children: /* @__PURE__ */ jsx("div", { className: "container mx-auto px-4 py-6", children: header }) }),
        /* @__PURE__ */ jsxs("main", { className: "", children: [
          /* @__PURE__ */ jsx(
            "div",
            {
              ref: animationInjection,
              className: "hidden w-[200px] h-[200px] fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50"
            }
          ),
          /* @__PURE__ */ jsx("div", { ref: section, className: "", children })
        ] }),
        /* @__PURE__ */ jsx(Footer, {})
      ]
    }
  ) });
}
const phone$1 = "Phone";
const phone_number$1 = "Phone Number";
const phone_placeholder$1 = "Enter phone number";
const welcome_message$1 = "Welcome to our store";
const shop_now$1 = "Shop Now";
const special_offers$1 = "Special Offers";
const new_arrivals$1 = "New Arrivals";
const confirm_password$1 = "Confirm Password";
const secure_area_confirmation$1 = "This is a secure area of the application. Please confirm your password before continuing.";
const password$1 = "Password";
const confirm$2 = "Confirm";
const login$1 = "Log in";
const login_description$1 = "Enter your information to access your account";
const email$1 = "Email";
const forgot_password$1 = "Forgot your password?";
const remember_me$1 = "Remember me";
const dont_have_account$1 = "Don't have an account?";
const register$1 = "Register";
const name$1 = "Name";
const create_account$1 = "Create an account";
const register_description$1 = "Enter your information to create an account";
const already_registered$1 = "Already registered?";
const forgot_password_description$1 = "Enter your email and we'll send you a link to reset your password.";
const back_to_login$1 = "Back to Login";
const send_reset_link$1 = "Email Password Reset Link";
const reset_password$1 = "Reset Password";
const reset_password_description$1 = "Create a new secure password for your account";
const verify_email$1 = "Email Verification";
const verify_email_description$1 = "Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?";
const verify_email_not_received$1 = "If you didn't receive the email, we will gladly send you another.";
const verification_link_sent$1 = "A new verification link has been sent to the email address you provided during registration.";
const resend_verification_email$1 = "Resend Verification Email";
const update_profile$1 = "Edit Profile";
const logout$1 = "Log Out";
const dashboard$1 = "Dashboard";
const logged_in_message$1 = "You're logged in!";
const profile$1 = "Profile";
const profile_information$1 = "Profile Information";
const update_profile_information$1 = "Update your account's profile information and email address.";
const save$1 = "Save";
const saved$1 = "Saved.";
const update_password$1 = "Update Password";
const ensure_account_security$1 = "Ensure your account is using a long, random password to stay secure.";
const current_password$1 = "Current Password";
const new_password$1 = "New Password";
const delete_account$1 = "Delete Account";
const delete_account_description$1 = "Once your account is deleted, all of its resources and data will be permanently deleted.";
const delete_account_confirmation$1 = "Are you sure you want to delete your account?";
const delete_account_warning$1 = "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.";
const cancel$1 = "Cancel";
const welcome$1 = "Welcome";
const search$1 = "Search";
const wishlist$1 = "Wishlist";
const cart$1 = "Cart";
const home$1 = "Home";
const categories$1 = "Categories";
const brands$1 = "Brands";
const no_brands_available$1 = "No brands available";
const no_brands_available_description$1 = "There are no brands available at the moment.";
const view_all_brands$1 = "View All Brands";
const shop_by_brand$1 = "Shop by Brand";
const featured_brands$1 = "Featured Brands";
const explore_brand_collection$1 = "Explore Brand Collection";
const men = "Men";
const women = "Women";
const kids = "Kids";
const account = "Account";
const no_categories_available$1 = "No categories available";
const best_sellers$1 = "Best Sellers";
const contact$1 = "Contact";
const language$1 = "Language";
const switch_language$1 = "Switch Language";
const my_orders$1 = "My Orders";
const log_out = "Logout";
const name_min_length$1 = "Name must be at least 2 characters.";
const valid_email$1 = "Please enter a valid email address.";
const current_password_required$1 = "Current password is required";
const password_min_length$1 = "Password must be at least 8 characters";
const confirm_password_required$1 = "Please confirm your password";
const passwords_dont_match$1 = "Passwords don't match";
const password_updated$1 = "Password updated successfully.";
const password_required_deletion$1 = "Password is required to confirm deletion";
const in_stock$1 = "In Stock";
const available$1 = "available";
const out_of_stock$1 = "Out of Stock";
const adding$1 = "Adding...";
const category$1 = "Category";
const description$1 = "Description";
const no_description$1 = "No description available.";
const dimensions_specifications$1 = "Dimensions & Specifications";
const quantity$1 = "Quantity";
const add_to_cart$1 = "Add to Cart";
const added_to_cart$1 = "Added to Cart";
const add_to_wishlist$1 = "Add to Wishlist";
const remove_from_wishlist$1 = "Remove from Wishlist";
const specifications$1 = "Specifications";
const no_specifications$1 = "No specifications available.";
const reviews$1 = "Reviews";
const no_reviews$1 = "No reviews yet.";
const be_first_to_review$1 = "Be the first to review this product!";
const related_products$1 = "Related Products";
const no_related_products$1 = "No related products found";
const off$1 = "OFF";
const product_description_empty$1 = "This product doesn't have a description yet";
const product_specifications_empty$1 = "This product doesn't have any specifications yet";
const checkout$1 = "Checkout";
const back_to_cart$1 = "Back to Cart";
const select_address$1 = "Please select a shipping address";
const no_addresses$1 = "You have no saved addresses. Please add an address to continue.";
const add_address$1 = "Add Address";
const order_notes$1 = "Order Notes";
const order_notes_placeholder$1 = "Add any special instructions for your order or delivery";
const order_summary$1 = "Order Summary";
const place_order$1 = "Place Order";
const placing_order$1 = "Placing Order...";
const order_terms$1 = "By placing this order, you agree to our Terms and Conditions";
const no_orders_yet$1 = "No Orders Yet";
const no_orders_description$1 = "You haven't placed any orders yet. Start shopping to place your first order!";
const start_shopping$1 = "Start Shopping";
const order_number$1 = "Order";
const order_status$1 = "Order Status";
const order_status_processing$1 = "Processing";
const order_status_shipped$1 = "Shipped";
const order_status_delivered$1 = "Delivered";
const order_status_cancelled$1 = "Cancelled";
const payment_status$1 = "Payment Status";
const payment_status_pending$1 = "Pending";
const payment_status_paid$1 = "Paid";
const items$1 = "Items";
const total$1 = "Total";
const payment_method$1 = "Payment Method";
const payment_method_cash_on_delivery$1 = "Cash on Delivery";
const view_details$1 = "View Details";
const order_details$1 = "Order Details";
const back_to_orders$1 = "Back to Orders";
const order_date$1 = "Order Date";
const coupon$1 = "Coupon";
const notes$1 = "Notes";
const shipping_address$1 = "Shipping Address";
const address_not_available$1 = "Address information not available";
const order_status_processing_title$1 = "Your order is being processed";
const order_status_shipped_title$1 = "Your order has been shipped";
const order_status_delivered_title$1 = "Your order has been delivered";
const order_status_cancelled_title$1 = "Your order has been cancelled";
const order_status_processing_description$1 = "We're preparing your order for shipment";
const order_status_shipped_description$1 = "Your order is on the way to you";
const order_status_delivered_description$1 = "Your order has been delivered successfully";
const order_status_cancelled_description$1 = "This order has been cancelled";
const order_items$1 = "Order Items";
const product_not_available$1 = "Product not available";
const subtotal$1 = "Subtotal";
const shipping$1 = "Shipping";
const discount$1 = "Discount";
const cancel_order$1 = "Cancel Order";
const complete_payment$1 = "Complete Payment";
const complete_your_payment$1 = "Complete Your Payment";
const confirm_cancel_order$1 = "Are you sure you want to cancel this order?";
const cancellation_policy_title$1 = "Cancellation Policy";
const cancellation_policy_desc$1 = "You can cancel your order while it's still in the processing stage. Once the order status changes to 'Shipped', it can no longer be cancelled.";
const weight$1 = "Weight";
const length$1 = "Length";
const width$1 = "Width";
const height$1 = "Height";
const dimension$1 = "Dimension";
const search_products$1 = "Search Products";
const search_placeholder$1 = "Type to search...";
const search_results$1 = "Search Results";
const results_for$1 = "Results for: '{{query}}'";
const view_all_results$1 = "View all results";
const view_all_results_for$1 = "View all results for '{{query}}'";
const searching$1 = "Searching...";
const no_results$1 = "No results found";
const no_results_found$1 = "No results found";
const try_different_search$1 = "Try a different search term";
const try_different_keywords$1 = "Try different keywords or check your spelling";
const clear$1 = "Clear";
const filters$1 = "Filters";
const filter_description$1 = "Filter products by brand, category or price range";
const reset_all$1 = "Reset all";
const reset_price$1 = "Reset Price";
const price_range$1 = "Price Range";
const min_price$1 = "Min Price";
const max_price$1 = "Max Price";
const price_range_from_to$1 = "Range: {{min}} - {{max}}";
const apply_filters$1 = "Apply Filters";
const filters_applied$1 = "Filters applied";
const sort_by$1 = "Sort by";
const newest$1 = "Newest";
const price_low_high$1 = "Price: Low to High";
const price_high_low$1 = "Price: High to Low";
const name_a_z$1 = "Name: A to Z";
const name_z_a$1 = "Name: Z to A";
const clearing$1 = "Clearing...";
const clear_wishlist$1 = "Clear Wishlist";
const wishlist_empty$1 = "Your wishlist is empty";
const wishlist_empty_description$1 = "Items added to your wishlist will appear here";
const all_categories$1 = "All Categories";
const view_all_categories$1 = "View All Categories";
const shop_by_category$1 = "Shop by Category";
const no_categories_available_description$1 = "There are no categories available at the moment.";
const explore_category$1 = "Explore Category";
const order_progress$1 = "Order Progress";
const order_cancelled$1 = "Order Cancelled";
const order_cancelled_message$1 = "This order has been cancelled and will not be processed further.";
const per_unit$1 = "per unit";
const variant$1 = "Variant";
const no_items$1 = "No items in this order";
const empty_order$1 = "This order doesn't contain any items";
const thank_you$1 = "Thank You for Your Order!";
const order_support$1 = "If you have any questions about your order, please contact our support team.";
const your_addresses$1 = "Your Addresses";
const add_new$1 = "Add New";
const add_new_address$1 = "Add New Address";
const address_description$1 = "Enter your address details below to create a new shipping address.";
const area$1 = "Area";
const select_area$1 = "Select area";
const loading$1 = "Loading...";
const no_areas$1 = "No areas available";
const address_details$1 = "Address Details";
const address_placeholder$1 = "Building, street, landmark...";
const saving_address$1 = "Saving...";
const save_address$1 = "Save Address";
const processing_payment$1 = "Processing Payment";
const back_to_order$1 = "Back to Order";
const initializing_payment$1 = "Initializing Payment";
const payment_message$1 = "Please wait while we connect to the secure payment gateway. Do not close this page.";
const payment_help_title$1 = "Having trouble with payment?";
const payment_help_message$1 = "If the payment window does not appear, please try refreshing this page. For assistance, contact our support team.";
const payment_method_credit_card$1 = "Credit Card";
const payment_method_wallet$1 = "Digital Wallet";
const payment_completed$1 = "Payment Completed";
const payment_failed$1 = "Payment Failed";
const payment_verification_failed$1 = "Payment verification failed";
const try_again$1 = "Please try again or use a different payment method";
const payment_contact_support$1 = "If the issue persists, please contact our support team.";
const coupon_code$1 = "Coupon Code";
const enter_coupon_code$1 = "Enter coupon code";
const removing$1 = "Removing...";
const remove$1 = "Remove";
const applying$1 = "Applying...";
const apply$1 = "Apply";
const promotion_applied$1 = "Promotion Applied Successfully!";
const code$1 = "Code";
const saving$1 = "Saving";
const address_required$1 = "Address Required";
const select_address_first$1 = "Please select an address first";
const coupon_required$1 = "Coupon Code Required";
const invalid_coupon_code$1 = "Invalid coupon code. Please check and try again.";
const coupon_error$1 = "Failed to apply coupon code. Please try again.";
const notes_placeholder$1 = "Add any special instructions or notes for your order...";
const privacy_policy$1 = "Privacy Policy";
const return_policy$1 = "Return Policy";
const terms_of_service$1 = "Terms of Service";
const facebook_data_deletion$1 = "Facebook Data Deletion";
const all_rights_reserved$1 = "All rights reserved";
const quick_links$1 = "Quick Links";
const follow_us$1 = "Follow Us";
const footer_description$1 = "Your trusted online shopping destination";
const language_preferences$1 = "Language Preferences";
const language_preferences_description$1 = "Choose your preferred language for the interface.";
const select_language$1 = "Select Language";
const or_continue_with$1 = "Or continue with";
const return_history$1 = "Return to History";
const enTranslations = {
  phone: phone$1,
  phone_number: phone_number$1,
  phone_placeholder: phone_placeholder$1,
  welcome_message: welcome_message$1,
  shop_now: shop_now$1,
  special_offers: special_offers$1,
  new_arrivals: new_arrivals$1,
  confirm_password: confirm_password$1,
  secure_area_confirmation: secure_area_confirmation$1,
  password: password$1,
  confirm: confirm$2,
  login: login$1,
  login_description: login_description$1,
  email: email$1,
  forgot_password: forgot_password$1,
  remember_me: remember_me$1,
  dont_have_account: dont_have_account$1,
  register: register$1,
  name: name$1,
  create_account: create_account$1,
  register_description: register_description$1,
  already_registered: already_registered$1,
  forgot_password_description: forgot_password_description$1,
  back_to_login: back_to_login$1,
  send_reset_link: send_reset_link$1,
  reset_password: reset_password$1,
  reset_password_description: reset_password_description$1,
  verify_email: verify_email$1,
  verify_email_description: verify_email_description$1,
  verify_email_not_received: verify_email_not_received$1,
  verification_link_sent: verification_link_sent$1,
  resend_verification_email: resend_verification_email$1,
  update_profile: update_profile$1,
  logout: logout$1,
  dashboard: dashboard$1,
  logged_in_message: logged_in_message$1,
  profile: profile$1,
  profile_information: profile_information$1,
  update_profile_information: update_profile_information$1,
  save: save$1,
  saved: saved$1,
  update_password: update_password$1,
  ensure_account_security: ensure_account_security$1,
  current_password: current_password$1,
  new_password: new_password$1,
  delete_account: delete_account$1,
  delete_account_description: delete_account_description$1,
  delete_account_confirmation: delete_account_confirmation$1,
  delete_account_warning: delete_account_warning$1,
  cancel: cancel$1,
  welcome: welcome$1,
  search: search$1,
  wishlist: wishlist$1,
  cart: cart$1,
  home: home$1,
  categories: categories$1,
  brands: brands$1,
  no_brands_available: no_brands_available$1,
  no_brands_available_description: no_brands_available_description$1,
  view_all_brands: view_all_brands$1,
  shop_by_brand: shop_by_brand$1,
  featured_brands: featured_brands$1,
  explore_brand_collection: explore_brand_collection$1,
  men,
  women,
  kids,
  account,
  no_categories_available: no_categories_available$1,
  best_sellers: best_sellers$1,
  contact: contact$1,
  language: language$1,
  switch_language: switch_language$1,
  my_orders: my_orders$1,
  log_out,
  name_min_length: name_min_length$1,
  valid_email: valid_email$1,
  current_password_required: current_password_required$1,
  password_min_length: password_min_length$1,
  confirm_password_required: confirm_password_required$1,
  passwords_dont_match: passwords_dont_match$1,
  password_updated: password_updated$1,
  password_required_deletion: password_required_deletion$1,
  in_stock: in_stock$1,
  available: available$1,
  out_of_stock: out_of_stock$1,
  adding: adding$1,
  category: category$1,
  description: description$1,
  no_description: no_description$1,
  dimensions_specifications: dimensions_specifications$1,
  quantity: quantity$1,
  add_to_cart: add_to_cart$1,
  added_to_cart: added_to_cart$1,
  add_to_wishlist: add_to_wishlist$1,
  remove_from_wishlist: remove_from_wishlist$1,
  specifications: specifications$1,
  no_specifications: no_specifications$1,
  reviews: reviews$1,
  no_reviews: no_reviews$1,
  be_first_to_review: be_first_to_review$1,
  related_products: related_products$1,
  no_related_products: no_related_products$1,
  off: off$1,
  product_description_empty: product_description_empty$1,
  product_specifications_empty: product_specifications_empty$1,
  checkout: checkout$1,
  back_to_cart: back_to_cart$1,
  select_address: select_address$1,
  no_addresses: no_addresses$1,
  add_address: add_address$1,
  order_notes: order_notes$1,
  order_notes_placeholder: order_notes_placeholder$1,
  order_summary: order_summary$1,
  place_order: place_order$1,
  placing_order: placing_order$1,
  order_terms: order_terms$1,
  no_orders_yet: no_orders_yet$1,
  no_orders_description: no_orders_description$1,
  start_shopping: start_shopping$1,
  order_number: order_number$1,
  order_status: order_status$1,
  order_status_processing: order_status_processing$1,
  order_status_shipped: order_status_shipped$1,
  order_status_delivered: order_status_delivered$1,
  order_status_cancelled: order_status_cancelled$1,
  payment_status: payment_status$1,
  payment_status_pending: payment_status_pending$1,
  payment_status_paid: payment_status_paid$1,
  items: items$1,
  total: total$1,
  payment_method: payment_method$1,
  payment_method_cash_on_delivery: payment_method_cash_on_delivery$1,
  view_details: view_details$1,
  order_details: order_details$1,
  back_to_orders: back_to_orders$1,
  order_date: order_date$1,
  coupon: coupon$1,
  notes: notes$1,
  shipping_address: shipping_address$1,
  address_not_available: address_not_available$1,
  order_status_processing_title: order_status_processing_title$1,
  order_status_shipped_title: order_status_shipped_title$1,
  order_status_delivered_title: order_status_delivered_title$1,
  order_status_cancelled_title: order_status_cancelled_title$1,
  order_status_processing_description: order_status_processing_description$1,
  order_status_shipped_description: order_status_shipped_description$1,
  order_status_delivered_description: order_status_delivered_description$1,
  order_status_cancelled_description: order_status_cancelled_description$1,
  order_items: order_items$1,
  product_not_available: product_not_available$1,
  subtotal: subtotal$1,
  shipping: shipping$1,
  discount: discount$1,
  cancel_order: cancel_order$1,
  complete_payment: complete_payment$1,
  complete_your_payment: complete_your_payment$1,
  confirm_cancel_order: confirm_cancel_order$1,
  cancellation_policy_title: cancellation_policy_title$1,
  cancellation_policy_desc: cancellation_policy_desc$1,
  weight: weight$1,
  length: length$1,
  width: width$1,
  height: height$1,
  dimension: dimension$1,
  search_products: search_products$1,
  search_placeholder: search_placeholder$1,
  search_results: search_results$1,
  results_for: results_for$1,
  view_all_results: view_all_results$1,
  view_all_results_for: view_all_results_for$1,
  searching: searching$1,
  no_results: no_results$1,
  no_results_found: no_results_found$1,
  try_different_search: try_different_search$1,
  try_different_keywords: try_different_keywords$1,
  clear: clear$1,
  filters: filters$1,
  filter_description: filter_description$1,
  reset_all: reset_all$1,
  reset_price: reset_price$1,
  price_range: price_range$1,
  min_price: min_price$1,
  max_price: max_price$1,
  price_range_from_to: price_range_from_to$1,
  apply_filters: apply_filters$1,
  filters_applied: filters_applied$1,
  sort_by: sort_by$1,
  newest: newest$1,
  price_low_high: price_low_high$1,
  price_high_low: price_high_low$1,
  name_a_z: name_a_z$1,
  name_z_a: name_z_a$1,
  clearing: clearing$1,
  clear_wishlist: clear_wishlist$1,
  wishlist_empty: wishlist_empty$1,
  wishlist_empty_description: wishlist_empty_description$1,
  all_categories: all_categories$1,
  view_all_categories: view_all_categories$1,
  shop_by_category: shop_by_category$1,
  no_categories_available_description: no_categories_available_description$1,
  explore_category: explore_category$1,
  order_progress: order_progress$1,
  order_cancelled: order_cancelled$1,
  order_cancelled_message: order_cancelled_message$1,
  per_unit: per_unit$1,
  variant: variant$1,
  no_items: no_items$1,
  empty_order: empty_order$1,
  thank_you: thank_you$1,
  order_support: order_support$1,
  your_addresses: your_addresses$1,
  add_new: add_new$1,
  add_new_address: add_new_address$1,
  address_description: address_description$1,
  area: area$1,
  select_area: select_area$1,
  loading: loading$1,
  no_areas: no_areas$1,
  address_details: address_details$1,
  address_placeholder: address_placeholder$1,
  saving_address: saving_address$1,
  save_address: save_address$1,
  processing_payment: processing_payment$1,
  back_to_order: back_to_order$1,
  initializing_payment: initializing_payment$1,
  payment_message: payment_message$1,
  payment_help_title: payment_help_title$1,
  payment_help_message: payment_help_message$1,
  payment_method_credit_card: payment_method_credit_card$1,
  payment_method_wallet: payment_method_wallet$1,
  payment_completed: payment_completed$1,
  payment_failed: payment_failed$1,
  payment_verification_failed: payment_verification_failed$1,
  try_again: try_again$1,
  payment_contact_support: payment_contact_support$1,
  coupon_code: coupon_code$1,
  enter_coupon_code: enter_coupon_code$1,
  removing: removing$1,
  remove: remove$1,
  applying: applying$1,
  apply: apply$1,
  promotion_applied: promotion_applied$1,
  code: code$1,
  saving: saving$1,
  address_required: address_required$1,
  select_address_first: select_address_first$1,
  coupon_required: coupon_required$1,
  invalid_coupon_code: invalid_coupon_code$1,
  coupon_error: coupon_error$1,
  notes_placeholder: notes_placeholder$1,
  privacy_policy: privacy_policy$1,
  return_policy: return_policy$1,
  terms_of_service: terms_of_service$1,
  facebook_data_deletion: facebook_data_deletion$1,
  all_rights_reserved: all_rights_reserved$1,
  quick_links: quick_links$1,
  follow_us: follow_us$1,
  footer_description: footer_description$1,
  language_preferences: language_preferences$1,
  language_preferences_description: language_preferences_description$1,
  select_language: select_language$1,
  or_continue_with: or_continue_with$1,
  return_history: return_history$1
};
const phone = "رقم الهاتف";
const phone_number = "رقم الهاتف";
const phone_placeholder = "أدخل رقم الهاتف";
const welcome_message = "مرحبًا بكم في متجرنا";
const shop_now = "تسوق الآن";
const special_offers = "عروض خاصة";
const new_arrivals = "وصل حديثًا";
const confirm_password = "تأكيد كلمة المرور";
const secure_area_confirmation = "هذه منطقة آمنة من التطبيق. يرجى تأكيد كلمة المرور الخاصة بك قبل المتابعة.";
const password = "كلمة المرور";
const confirm$1 = "تأكيد";
const login = "تسجيل الدخول";
const login_description = "أدخل معلوماتك للوصول إلى حسابك";
const email = "البريد الإلكتروني";
const forgot_password = "نسيت كلمة المرور؟";
const remember_me = "تذكرني";
const dont_have_account = "ليس لديك حساب؟";
const register = "تسجيل";
const name = "الاسم";
const create_account = "إنشاء حساب";
const register_description = "أدخل معلوماتك لإنشاء حساب";
const already_registered = "مسجل بالفعل؟";
const forgot_password_description = "أدخل بريدك الإلكتروني وسنرسل لك رابطًا لإعادة تعيين كلمة المرور.";
const back_to_login = "العودة إلى تسجيل الدخول";
const send_reset_link = "إرسال رابط إعادة تعيين كلمة المرور";
const reset_password = "إعادة تعيين كلمة المرور";
const reset_password_description = "إنشاء كلمة مرور جديدة آمنة لحسابك";
const verify_email = "التحقق من البريد الإلكتروني";
const verify_email_description = "شكراً للتسجيل! قبل البدء، هل يمكنك التحقق من عنوان بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه إليك؟";
const verify_email_not_received = "إذا لم تتلق البريد الإلكتروني، فسنرسل لك رسالة أخرى بكل سرور.";
const verification_link_sent = "تم إرسال رابط تحقق جديد إلى عنوان البريد الإلكتروني الذي قدمته أثناء التسجيل.";
const resend_verification_email = "إعادة إرسال بريد التحقق";
const update_profile = "تعديل الملف الشخصي";
const logout = "تسجيل الخروج";
const dashboard = "لوحة التحكم";
const logged_in_message = "لقد قمت بتسجيل الدخول!";
const profile = "الملف الشخصي";
const profile_information = "معلومات الملف الشخصي";
const update_profile_information = "تحديث معلومات الملف الشخصي وعنوان البريد الإلكتروني لحسابك.";
const save = "حفظ";
const saved = "تم الحفظ.";
const update_password = "تحديث كلمة المرور";
const ensure_account_security = "تأكد من أن حسابك يستخدم كلمة مرور طويلة وعشوائية للحفاظ على الأمان.";
const current_password = "كلمة المرور الحالية";
const new_password = "كلمة المرور الجديدة";
const delete_account = "حذف الحساب";
const delete_account_description = "بمجرد حذف حسابك، سيتم حذف جميع موارده وبياناته بشكل دائم.";
const delete_account_confirmation = "هل أنت متأكد أنك تريد حذف حسابك؟";
const delete_account_warning = "بمجرد حذف حسابك، سيتم حذف جميع موارده وبياناته نهائيًا. قبل حذف حسابك، يرجى تنزيل أي بيانات أو معلومات ترغب في الاحتفاظ بها.";
const cancel = "إلغاء";
const welcome = "مرحبًا";
const search = "بحث";
const wishlist = "المفضلة";
const cart = "عربة التسوق";
const home = "الرئيسية";
const categories = "الفئات";
const brands = "العلامات التجارية";
const no_brands_available = "لا توجد علامات تجارية متاحة";
const no_brands_available_description = "لا توجد علامات تجارية متاحة في الوقت الحالي";
const view_all_brands = "عرض كل العلامات التجارية";
const shop_by_brand = "تسوق حسب العلامة التجارية";
const featured_brands = "العلامات التجارية المميزة";
const explore_brand_collection = "استكشف مجموعة العلامة التجارية";
const best_sellers = "الأكثر مبيعاً";
const contact = "اتصل بنا";
const language = "اللغة";
const switch_language = "تغيير اللغة";
const shop_by_category = "تسوق حسب الفئة";
const our_brands = "علاماتنا التجارية";
const Home = "الرئيسية";
const best_offers = "أفضل العروض";
const no_offers_available = "لا توجد عروض متاحة";
const view_all = "عرض الكل";
const no_products_available = "لا توجد منتجات متاحة";
const featured_products = "منتجات مميزة";
const off = "خصم";
const premium_shopping_experience = "استمتع بتجربة تسوق مميزة مع فلاين";
const discover_premium_products = "اكتشف أفضل المنتجات مع عروض حصرية وجودة عالية. انضم إلى مجتمعنا اليوم.";
const explore_collection = "استكشف المجموعة";
const no_image = "لا توجد صورة";
const name_min_length = "يجب أن يكون الاسم على الأقل 2 حرفين.";
const valid_email = "يرجى إدخال عنوان بريد إلكتروني صالح.";
const current_password_required = "كلمة المرور الحالية مطلوبة";
const password_min_length = "يجب أن تكون كلمة المرور على الأقل 8 أحرف";
const confirm_password_required = "يرجى تأكيد كلمة المرور";
const passwords_dont_match = "كلمات المرور غير متطابقة";
const password_updated = "تم تحديث كلمة المرور بنجاح.";
const password_required_deletion = "كلمة المرور مطلوبة لتأكيد الحذف";
const in_stock = "متوفر";
const available = "متاح";
const out_of_stock = "غير متوفر";
const adding = "جارٍ الإضافة...";
const category = "الفئة";
const description = "الوصف";
const no_description = "لا يوجد وصف متاح.";
const dimensions_specifications = "الأبعاد والمواصفات";
const quantity = "الكمية";
const add_to_cart = "أضف إلى السلة";
const add_to_wishlist = "أضف إلى المفضلة";
const remove_from_wishlist = "إزالة من المفضلة";
const specifications = "المواصفات";
const no_specifications = "لا توجد مواصفات متاحة.";
const reviews = "التقييمات";
const no_reviews = "لا توجد تقييمات بعد.";
const be_first_to_review = "كن أول من يقيم هذا المنتج!";
const related_products = "منتجات ذات صلة";
const no_related_products = "لا توجد منتجات ذات صلة";
const product_description_empty = "هذا المنتج ليس لديه وصف بعد";
const product_specifications_empty = "هذا المنتج ليس لديه أي مواصفات بعد";
const weight = "الوزن";
const length = "الطول";
const width = "العرض";
const height = "الارتفاع";
const dimension = "البعد";
const shopping_cart = "عربة التسوق";
const cart_items_count = "لديك {{count}} عناصر في سلة التسوق";
const your_cart_is_empty = "سلة التسوق فارغة";
const add_items_to_cart = "أضف منتجات إلى سلة التسوق لعرضها هنا";
const continue_shopping = "مواصلة التسوق";
const no_items_available = "لا توجد عناصر متاحة";
const color = "اللون";
const size = "الحجم";
const capacity = "السعة";
const back = "رجوع";
const adding_to_cart = "جارٍ الإضافة إلى السلة...";
const address_required = "العنوان مطلوب";
const coupon_required = "رمز الكوبون مطلوب";
const items_in_cart = "العناصر في سلة التسوق";
const discount_applied = "تم تطبيق الخصم";
const processing_order = "جارٍ المعالجة...";
const removing = "جارٍ الإزالة...";
const applying = "جارٍ التطبيق...";
const code = "الرمز";
const return_request_information = "معلومات طلب الإرجاع";
const requested_on = "تم الطلب في";
const return_reason = "السبب";
const no_returns = "لا توجد طلبات إرجاع";
const no_returns_message = "لم تطلب أي إرجاع بعد";
const view_orders = "عرض الطلبات";
const price = "السعر";
const total = "المجموع";
const order_summary = "ملخص الطلب";
const subtotal = "المجموع الفرعي";
const shipping = "الشحن";
const free = "مجاني";
const calculated_at_checkout = "يتم حسابه عند الدفع";
const proceed_to_checkout = "المتابعة للدفع";
const clear_cart = "إفراغ السلة";
const search_products = "البحث عن المنتجات";
const search_placeholder = "اكتب للبحث...";
const search_results = "نتائج البحث";
const results_for = "نتائج البحث عن: '{{query}}'";
const view_all_results = "عرض جميع النتائج";
const view_all_results_for = "عرض جميع النتائج لـ '{{query}}'";
const searching = "جاري البحث...";
const no_results = "لم يتم العثور على نتائج";
const no_results_found = "لم يتم العثور على نتائج";
const try_different_search = "جرب كلمات بحث مختلفة";
const try_different_keywords = "جرب كلمات مفتاحية مختلفة أو تحقق من الإملاء";
const clear = "مسح";
const filters = "التصفية";
const filter_description = "تصفية المنتجات حسب العلامة التجارية أو الفئة أو نطاق السعر";
const reset_all = "إعادة تعيين الكل";
const reset_price = "إعادة تعيين السعر";
const price_range = "نطاق السعر";
const min_price = "الحد الأدنى للسعر";
const max_price = "الحد الأقصى للسعر";
const price_range_from_to = "النطاق: {{min}} - {{max}}";
const apply_filters = "تطبيق التصفية";
const filters_applied = "تم تطبيق التصفية";
const sort_by = "ترتيب حسب";
const newest = "الأحدث";
const price_low_high = "السعر: من الأقل إلى الأعلى";
const price_high_low = "السعر: من الأعلى إلى الأقل";
const name_a_z = "الاسم: من أ إلى ي";
const name_z_a = "الاسم: من ي إلى أ";
const clearing = "جاري المسح...";
const clear_wishlist = "مسح المفضلة";
const wishlist_empty = "قائمة المفضلة فارغة";
const wishlist_empty_description = "العناصر المضافة إلى المفضلة ستظهر هنا";
const all_categories = "جميع الفئات";
const view_all_categories = "عرض جميع الفئات";
const categories_shop_by = "تسوق حسب الفئة";
const no_categories_available = "لا توجد فئات متاحة";
const no_categories_available_description = "لا توجد فئات متاحة في الوقت الحالي";
const explore_category = "استكشف الفئة";
const my_orders = "طلباتي";
const no_orders_yet = "لا توجد طلبات بعد";
const no_orders_description = "لم تقم بأي طلبات حتى الآن. ابدأ التسوق لتقديم طلبك الأول!";
const start_shopping = "ابدأ التسوق";
const order_number = "الطلب رقم";
const order_status = "حالة الطلب";
const order_status_processing = "قيد المراجعة";
const order_status_shipped = "تم الشحن";
const order_status_delivered = "تم التوصيل";
const order_status_cancelled = "ملغي";
const payment_status = "حالة الدفع";
const payment_status_pending = "قيد الانتظار";
const payment_status_paid = "تم الدفع";
const items = "العناصر";
const payment_method = "طريقة الدفع";
const payment_method_cash_on_delivery = "الدفع عند الاستلام";
const view_details = "عرض التفاصيل";
const order_details = "تفاصيل الطلب";
const back_to_orders = "العودة إلى الطلبات";
const order_date = "تاريخ الطلب";
const coupon = "كوبون";
const notes = "ملاحظات";
const shipping_address = "عنوان الشحن";
const address_not_available = "معلومات العنوان غير متوفرة";
const order_status_processing_title = "طلبك قيد المعالجة";
const order_status_shipped_title = "تم شحن طلبك";
const order_status_delivered_title = "تم توصيل طلبك";
const order_status_cancelled_title = "تم إلغاء طلبك";
const order_status_processing_description = "نقوم بتحضير طلبك للشحن";
const order_status_shipped_description = "طلبك في الطريق إليك";
const order_status_delivered_description = "تم توصيل طلبك بنجاح";
const order_status_cancelled_description = "تم إلغاء هذا الطلب";
const order_items = "عناصر الطلب";
const product_not_available = "المنتج غير متوفر";
const discount = "الخصم";
const cancel_order = "إلغاء الطلب";
const complete_payment = "إكمال الدفع";
const complete_your_payment = "إكمال عملية الدفع";
const confirm_cancel_order = "هل أنت متأكد أنك تريد إلغاء هذا الطلب؟";
const cancellation_policy_title = "سياسة الإلغاء";
const cancellation_policy_desc = "يمكنك إلغاء طلبك بينما لا يزال في مرحلة المعالجة. بمجرد تغيير حالة الطلب إلى 'تم الشحن'، لا يمكن إلغاؤه.";
const checkout = "الدفع";
const back_to_cart = "العودة إلى سلة التسوق";
const added_to_cart = "تمت الإضافة إلى السلة";
const select_address = "يرجى تحديد عنوان الشحن";
const no_addresses = "ليس لديك عناوين محفوظة. الرجاء إضافة عنوان للمتابعة.";
const add_address = "إضافة عنوان";
const order_notes = "ملاحظات الطلب";
const order_notes_placeholder = "أضف أي تعليمات خاصة لطلبك أو التوصيل";
const place_order = "إتمام الطلب";
const placing_order = "جاري تقديم الطلب...";
const order_terms = "بتقديم هذا الطلب، فإنك توافق على الشروط والأحكام";
const order_progress = "تقدم الطلب";
const order_cancelled = "تم إلغاء الطلب";
const order_cancelled_message = "تم إلغاء هذا الطلب ولن يتم معالجته بعد الآن";
const per_unit = "للوحدة";
const variant = "المتغير";
const no_items = "لا توجد عناصر في هذا الطلب";
const empty_order = "لا يحتوي هذا الطلب على أي عناصر";
const thank_you = "شكراً لطلبك!";
const order_support = "إذا كان لديك أي أسئلة حول طلبك، يرجى الاتصال بفريق الدعم";
const your_addresses = "العناوين الخاصة بك";
const add_new = "إضافة جديد";
const add_new_address = "إضافة عنوان جديد";
const address_description = "أدخل تفاصيل عنوانك أدناه لإنشاء عنوان شحن جديد.";
const area = "المنطقة";
const select_area = "اختر المنطقة";
const loading = "جارٍ التحميل...";
const no_areas = "لا توجد مناطق متاحة";
const address_details = "تفاصيل العنوان";
const address_placeholder = "المبنى، الشارع، معلم بارز...";
const saving_address = "جارٍ الحفظ...";
const save_address = "حفظ العنوان";
const processing_payment = "معالجة الدفع";
const back_to_order = "العودة إلى الطلب";
const initializing_payment = "جارِ تهيئة الدفع";
const payment_message = "يرجى الانتظار أثناء الاتصال ببوابة الدفع الآمن. لا تغلق هذه الصفحة.";
const payment_help_title = "هل تواجه مشكلة في الدفع؟";
const payment_help_message = "إذا لم تظهر نافذة الدفع، يرجى تحديث هذه الصفحة. للمساعدة، اتصل بفريق الدعم لدينا.";
const payment_method_credit_card = "بطاقة ائتمانية";
const payment_method_wallet = "المحفظة الإلكترونية";
const payment_completed = "اكتمل الدفع";
const payment_failed = "فشل الدفع";
const payment_verification_failed = "فشل التحقق من الدفع";
const try_again = "يرجى المحاولة مرة أخرى أو استخدام طريقة دفع مختلفة";
const payment_contact_support = "إذا استمرت المشكلة، يرجى الاتصال بفريق الدعم.";
const coupon_code = "كود الكوبون";
const enter_coupon_code = "أدخل كود الكوبون";
const remove = "إزالة";
const apply = "تطبيق";
const promotion_applied = "تم تطبيق العرض الترويجي بنجاح!";
const saving = "جارٍ الحفظ";
const select_address_first = "يرجى تحديد عنوان أولاً";
const invalid_coupon_code = "كود الكوبون غير صالح. يرجى التحقق والمحاولة مرة أخرى.";
const coupon_error = "فشل في تطبيق كود الكوبون. يرجى المحاولة مرة أخرى.";
const notes_placeholder = "أضف أي تعليمات أو ملاحظات خاصة لطلبك...";
const privacy_policy = "سياسة الخصوصية";
const return_policy = "سياسة الإرجاع";
const terms_of_service = "شروط الخدمة";
const facebook_data_deletion = "حذف بيانات فيسبوك";
const all_rights_reserved = "جميع الحقوق محفوظة";
const quick_links = "روابط سريعة";
const follow_us = "تابعنا";
const footer_description = "وجهتك المضمونة للتسوق عبر الإنترنت";
const language_preferences = "تفضيلات اللغة";
const language_preferences_description = "اختر لغتك المفضلة لواجهة الموقع.";
const select_language = "اختر اللغة";
const or_continue_with = "أو المتابعة باستخدام";
const return_history = "العودة إلى السجل";
const arTranslations = {
  phone,
  phone_number,
  phone_placeholder,
  welcome_message,
  shop_now,
  special_offers,
  new_arrivals,
  confirm_password,
  secure_area_confirmation,
  password,
  confirm: confirm$1,
  login,
  login_description,
  email,
  forgot_password,
  remember_me,
  dont_have_account,
  register,
  name,
  create_account,
  register_description,
  already_registered,
  forgot_password_description,
  back_to_login,
  send_reset_link,
  reset_password,
  reset_password_description,
  verify_email,
  verify_email_description,
  verify_email_not_received,
  verification_link_sent,
  resend_verification_email,
  update_profile,
  logout,
  dashboard,
  logged_in_message,
  profile,
  profile_information,
  update_profile_information,
  save,
  saved,
  update_password,
  ensure_account_security,
  current_password,
  new_password,
  delete_account,
  delete_account_description,
  delete_account_confirmation,
  delete_account_warning,
  cancel,
  welcome,
  search,
  wishlist,
  cart,
  home,
  categories,
  brands,
  no_brands_available,
  no_brands_available_description,
  view_all_brands,
  shop_by_brand,
  featured_brands,
  explore_brand_collection,
  best_sellers,
  contact,
  language,
  switch_language,
  shop_by_category,
  our_brands,
  Home,
  "Featured Brands": "العلامات التجارية المميزة",
  "No brands available": "لا توجد علامات تجارية متاحة",
  "Shop by Category": "تسوق حسب الفئة",
  "No categories available": "لا توجد فئات متاحة",
  best_offers,
  no_offers_available,
  view_all,
  no_products_available,
  featured_products,
  off,
  premium_shopping_experience,
  discover_premium_products,
  explore_collection,
  no_image,
  name_min_length,
  valid_email,
  current_password_required,
  password_min_length,
  confirm_password_required,
  passwords_dont_match,
  password_updated,
  password_required_deletion,
  in_stock,
  available,
  out_of_stock,
  adding,
  category,
  description,
  no_description,
  dimensions_specifications,
  quantity,
  add_to_cart,
  add_to_wishlist,
  remove_from_wishlist,
  specifications,
  no_specifications,
  reviews,
  no_reviews,
  be_first_to_review,
  related_products,
  no_related_products,
  product_description_empty,
  product_specifications_empty,
  weight,
  length,
  width,
  height,
  dimension,
  shopping_cart,
  cart_items_count,
  your_cart_is_empty,
  add_items_to_cart,
  continue_shopping,
  no_items_available,
  color,
  size,
  capacity,
  back,
  adding_to_cart,
  address_required,
  coupon_required,
  items_in_cart,
  discount_applied,
  processing_order,
  removing,
  applying,
  code,
  return_request_information,
  requested_on,
  return_reason,
  no_returns,
  no_returns_message,
  view_orders,
  price,
  total,
  order_summary,
  subtotal,
  shipping,
  free,
  calculated_at_checkout,
  proceed_to_checkout,
  clear_cart,
  search_products,
  search_placeholder,
  search_results,
  results_for,
  view_all_results,
  view_all_results_for,
  searching,
  no_results,
  no_results_found,
  try_different_search,
  try_different_keywords,
  clear,
  filters,
  filter_description,
  reset_all,
  reset_price,
  price_range,
  min_price,
  max_price,
  price_range_from_to,
  apply_filters,
  filters_applied,
  sort_by,
  newest,
  price_low_high,
  price_high_low,
  name_a_z,
  name_z_a,
  clearing,
  clear_wishlist,
  wishlist_empty,
  wishlist_empty_description,
  all_categories,
  view_all_categories,
  categories_shop_by,
  no_categories_available,
  no_categories_available_description,
  explore_category,
  my_orders,
  no_orders_yet,
  no_orders_description,
  start_shopping,
  order_number,
  order_status,
  order_status_processing,
  order_status_shipped,
  order_status_delivered,
  order_status_cancelled,
  payment_status,
  payment_status_pending,
  payment_status_paid,
  items,
  payment_method,
  payment_method_cash_on_delivery,
  view_details,
  order_details,
  back_to_orders,
  order_date,
  coupon,
  notes,
  shipping_address,
  address_not_available,
  order_status_processing_title,
  order_status_shipped_title,
  order_status_delivered_title,
  order_status_cancelled_title,
  order_status_processing_description,
  order_status_shipped_description,
  order_status_delivered_description,
  order_status_cancelled_description,
  order_items,
  product_not_available,
  discount,
  cancel_order,
  complete_payment,
  complete_your_payment,
  confirm_cancel_order,
  cancellation_policy_title,
  cancellation_policy_desc,
  checkout,
  back_to_cart,
  added_to_cart,
  select_address,
  no_addresses,
  add_address,
  order_notes,
  order_notes_placeholder,
  place_order,
  placing_order,
  order_terms,
  order_progress,
  order_cancelled,
  order_cancelled_message,
  per_unit,
  variant,
  no_items,
  empty_order,
  thank_you,
  order_support,
  your_addresses,
  add_new,
  add_new_address,
  address_description,
  area,
  select_area,
  loading,
  no_areas,
  address_details,
  address_placeholder,
  saving_address,
  save_address,
  processing_payment,
  back_to_order,
  initializing_payment,
  payment_message,
  payment_help_title,
  payment_help_message,
  payment_method_credit_card,
  payment_method_wallet,
  payment_completed,
  payment_failed,
  payment_verification_failed,
  try_again,
  payment_contact_support,
  coupon_code,
  enter_coupon_code,
  remove,
  apply,
  promotion_applied,
  saving,
  select_address_first,
  invalid_coupon_code,
  coupon_error,
  notes_placeholder,
  privacy_policy,
  return_policy,
  terms_of_service,
  facebook_data_deletion,
  all_rights_reserved,
  quick_links,
  follow_us,
  footer_description,
  language_preferences,
  language_preferences_description,
  select_language,
  or_continue_with,
  return_history
};
i18n.use(LanguageDetector).use(initReactI18next).init({
  debug: false,
  fallbackLng: "en",
  supportedLngs: ["en", "ar"],
  // Language detection options
  detection: {
    order: ["localStorage", "navigator", "htmlTag"],
    caches: ["localStorage"],
    lookupLocalStorage: "language"
  },
  interpolation: {
    escapeValue: false
  },
  resources: {
    en: {
      translation: enTranslations
    },
    ar: {
      translation: arTranslations
    }
  },
  // Handle RTL languages
  postProcess: ["react-i18next"]
});
i18n.on("languageChanged", (lng) => {
  document.documentElement.lang = lng;
  document.documentElement.dir = lng === "ar" ? "rtl" : "ltr";
});
const appName = "Moda";
createServer(
  (page) => createInertiaApp({
    page,
    render: ReactDOMServer.renderToString,
    title: (title) => `${title} - ${appName}`,
    resolve: (name2) => {
      const pages = /* @__PURE__ */ Object.assign({ "./Pages/Auth/ConfirmPassword.tsx": __vite_glob_0_0, "./Pages/Auth/ForgotPassword.tsx": __vite_glob_0_1, "./Pages/Auth/Login.tsx": __vite_glob_0_2, "./Pages/Auth/Register.tsx": __vite_glob_0_3, "./Pages/Auth/ResetPassword.tsx": __vite_glob_0_4, "./Pages/Auth/VerifyEmail.tsx": __vite_glob_0_5, "./Pages/Brands/Index.tsx": __vite_glob_0_6, "./Pages/Cart/Index.tsx": __vite_glob_0_7, "./Pages/Categories/Index.tsx": __vite_glob_0_8, "./Pages/Checkout/Index.tsx": __vite_glob_0_9, "./Pages/Home.tsx": __vite_glob_0_10, "./Pages/Orders/Index.tsx": __vite_glob_0_11, "./Pages/Orders/ReturnHistory.tsx": __vite_glob_0_12, "./Pages/Orders/Show.tsx": __vite_glob_0_13, "./Pages/Pages/Contact.tsx": __vite_glob_0_14, "./Pages/Pages/FacebookDataDeletion.tsx": __vite_glob_0_15, "./Pages/Pages/Privacy.tsx": __vite_glob_0_16, "./Pages/Pages/Returns.tsx": __vite_glob_0_17, "./Pages/Pages/Terms.tsx": __vite_glob_0_18, "./Pages/Payments/Kashier.tsx": __vite_glob_0_19, "./Pages/Products/Section.tsx": __vite_glob_0_20, "./Pages/Products/Show.tsx": __vite_glob_0_21, "./Pages/Profile/Edit.tsx": __vite_glob_0_22, "./Pages/Profile/Partials/DeleteUserForm.tsx": __vite_glob_0_23, "./Pages/Profile/Partials/UpdatePasswordForm.tsx": __vite_glob_0_24, "./Pages/Profile/Partials/UpdateProfileInformationForm.tsx": __vite_glob_0_25, "./Pages/Search/Results.tsx": __vite_glob_0_26, "./Pages/Wishlist/Index.tsx": __vite_glob_0_27 });
      const pageComponent = pages[`./Pages/${name2}.tsx`];
      if (pageComponent && !pageComponent.default.layout) {
        pageComponent.default.layout = (page2) => /* @__PURE__ */ jsx(MainLayout, { children: page2 });
      }
      return pageComponent;
    },
    setup: ({ App, props }) => /* @__PURE__ */ jsxs(Fragment, { children: [
      /* @__PURE__ */ jsx(App, { ...props }),
      /* @__PURE__ */ jsx(Toaster, {})
    ] })
  })
);
