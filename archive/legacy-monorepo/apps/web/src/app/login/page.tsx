import LoginForm from "@/components/auth/LoginForm";

export default function LoginPage() {
  return (
    <main className="min-h-screen flex flex-col items-center justify-center bg-[#000000] text-white px-4">
      <div className="w-full max-w-sm flex flex-col items-center gap-8">
        {/* Logo COTA — identique à l'app Flutter */}
        <div className="text-center">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#F9FF00]/10 border border-[#F9FF00]/25 mb-4">
            <span className="text-[#F9FF00] text-2xl font-black">⚽</span>
          </div>
          <h1 className="text-4xl font-black tracking-tight text-white">COTA</h1>
          <p className="text-[#888888] mt-1 text-sm">Pronostics football IA</p>
        </div>

        {/* Carte formulaire */}
        <div className="w-full bg-[#111111] border border-[#1E1E1E] rounded-2xl p-6">
          <h2 className="text-lg font-bold text-white mb-1">Connexion</h2>
          <p className="text-sm text-[#888888] mb-6">Entre ton email pour recevoir un code</p>
          <LoginForm />
        </div>
      </div>
    </main>
  );
}
