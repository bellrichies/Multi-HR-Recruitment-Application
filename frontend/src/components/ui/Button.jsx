export function Button({ as: Component = 'button', className = '', variant = 'primary', ...props }) {
  const variants = {
    primary: 'bg-brand text-white hover:bg-teal-800',
    secondary: 'border border-line bg-white text-ink hover:bg-panel',
    danger: 'bg-danger text-white hover:bg-red-800',
    ghost: 'text-ink hover:bg-panel',
  };

  return (
    <Component
      className={`focus-ring inline-flex h-10 items-center justify-center gap-2 rounded-md px-4 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${className}`}
      {...props}
    />
  );
}
