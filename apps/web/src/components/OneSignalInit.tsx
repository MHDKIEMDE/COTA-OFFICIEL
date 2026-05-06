"use client";

import { useEffect } from "react";

declare global {
  interface Window {
    OneSignalDeferred?: ((os: OneSignalStatic) => void)[];
  }
  interface OneSignalStatic {
    init(options: {
      appId: string;
      safari_web_id?: string;
      notifyButton?: { enable: boolean };
      allowLocalhostAsSecureOrigin?: boolean;
    }): void;
    Notifications: {
      requestPermission(): Promise<boolean>;
    };
  }
}

export default function OneSignalInit() {
  useEffect(() => {
    const appId = process.env.NEXT_PUBLIC_ONESIGNAL_APP_ID;
    if (!appId) return;

    window.OneSignalDeferred = window.OneSignalDeferred ?? [];
    window.OneSignalDeferred.push((os) => {
      os.init({
        appId,
        notifyButton: { enable: false },
        allowLocalhostAsSecureOrigin: process.env.NODE_ENV === "development",
      });
    });

    const script = document.createElement("script");
    script.src = "https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js";
    script.defer = true;
    document.head.appendChild(script);

    return () => {
      document.head.removeChild(script);
    };
  }, []);

  return null;
}
