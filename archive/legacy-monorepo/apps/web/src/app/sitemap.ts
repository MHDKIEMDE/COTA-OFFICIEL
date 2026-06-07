import type { MetadataRoute } from "next";

export default function sitemap(): MetadataRoute.Sitemap {
  const base = "https://cota.app";
  const now = new Date();

  return [
    { url: base,                      lastModified: now, changeFrequency: "daily",   priority: 1.0 },
    { url: `${base}/predictions`,     lastModified: now, changeFrequency: "daily",   priority: 0.9 },
    { url: `${base}/coupon`,          lastModified: now, changeFrequency: "daily",   priority: 0.9 },
    { url: `${base}/bookmakers`,      lastModified: now, changeFrequency: "weekly",  priority: 0.7 },
    { url: `${base}/subscribe`,       lastModified: now, changeFrequency: "monthly", priority: 0.8 },
  ];
}
