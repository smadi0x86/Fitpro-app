import { resolve } from "path";
import { defineConfig } from "vite";
import rewriteAll from "vite-plugin-rewrite-all";

export default defineConfig({
  plugins: [rewriteAll()],
  root: resolve(__dirname, "src"),
  build: {
    outDir: resolve(__dirname, "dist"), // Output directory for build files
    rollupOptions: {
      input: {
        main: resolve(__dirname, "src/index.html"),
        cart: resolve(__dirname, "src/cart.html"),
        company: resolve(__dirname, "src/company.html"),
        emailsent: resolve(__dirname, "src/email-sent.html"),
        membership: resolve(__dirname, "src/membership.html"),
        paymentfailed: resolve(__dirname, "src/payment-failed.html"),
        paymentsuccess: resolve(__dirname, "src/payment-success.html"),
        profile: resolve(__dirname, "src/profile.html"),
        register: resolve(__dirname, "src/register.html"),
        services: resolve(__dirname, "src/services.html"),
        shop: resolve(__dirname, "src/shop.html"),
        signin: resolve(__dirname, "src/signin.html"),
        // Add more entries as needed
      },
    },
  },
  server: {
    port: 80,
    // // Configure proxy to backend API for development if needed
    // proxy: {
    //   // Proxying example if your backend is running on a different port
    //   "/api": "http://localhost:9501",
    // },
  },
});
