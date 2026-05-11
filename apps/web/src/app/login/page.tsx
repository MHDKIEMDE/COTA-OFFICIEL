import LoginForm from "@/components/auth/LoginForm";

export default function LoginPage() {
  return (
    <main className="min-h-screen flex flex-col items-center justify-center bg-gray-950 text-white px-4">
      <div className="w-full max-w-sm flex flex-col items-center gap-8">
        <div className="text-center">
          <h1 className="text-4xl font-black tracking-tight text-green-400">COTA</h1>
          <p className="text-gray-400 mt-1 text-sm">Pronostics football IA</p>
        </div>
        <LoginForm />
      </div>
    </main>
  );
}
