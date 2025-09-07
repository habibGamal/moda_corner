import { cn } from "@/lib/utils";
import { Loader2 } from "lucide-react";

interface SpinnerProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: "default" | "sm" | "lg";
}

export function Spinner({
  className,
  size = "default",
  ...props
}: SpinnerProps) {
  return (
    <div
      className={cn("animate-spin text-muted-foreground", {
        "w-4 h-4": size === "sm",
        "w-6 h-6": size === "default",
        "w-10 h-10": size === "lg",
      }, className)}
      {...props}
    >
      <Loader2 className="w-full h-full" />
      <span className="sr-only">Loading</span>
    </div>
  );
}
