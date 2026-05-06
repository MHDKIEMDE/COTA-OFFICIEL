import Link from "next/link";

export default function PremiumLock() {
  return (
    <div className="flex flex-col items-center justify-center gap-3 py-8 px-4 rounded-xl border border-yellow-500/30 bg-yellow-500/5">
      <div className="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center">
        <svg className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
        </svg>
      </div>
      <p className="text-sm text-gray-400 text-center">
        Contenu <span className="text-yellow-400 font-semibold">Premium</span>
      </p>
      <Link
        href="/subscribe"
        className="text-xs bg-yellow-500 hover:bg-yellow-400 text-black font-bold px-4 py-2 rounded-lg transition"
      >
        Débloquer Premium
      </Link>
    </div>
  );
}
