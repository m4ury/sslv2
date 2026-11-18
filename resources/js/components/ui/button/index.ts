import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all duration-200 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive active:scale-[0.98] shadow-xs hover:shadow-md",
  {
    variants: {
      variant: {
        default:
          "bg-linear-to-b from-primary to-primary/90 text-primary-foreground hover:from-primary hover:to-primary border border-primary/20",
        destructive:
          "bg-linear-to-b from-destructive to-destructive/90 text-white hover:from-destructive hover:to-destructive focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        outline:
          "border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50",
        secondary:
          "bg-linear-to-b from-secondary to-secondary/80 text-secondary-foreground hover:from-secondary hover:to-secondary",
        ghost:
          "hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 shadow-none hover:shadow-none",
        link: "text-primary underline-offset-4 hover:underline shadow-none hover:shadow-none",
        success:
          "bg-linear-to-b from-success to-success/90 text-success-foreground hover:from-success hover:to-success",
        warning:
          "bg-linear-to-b from-warning to-warning/90 text-warning-foreground hover:from-warning hover:to-warning",
        info:
          "bg-linear-to-b from-info to-info/90 text-info-foreground hover:from-info hover:to-info",
      },
      size: {
        "default": "h-9 px-4 py-2 has-[>svg]:px-3",
        "sm": "h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5",
        "lg": "h-10 rounded-md px-6 has-[>svg]:px-4",
        "icon": "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
