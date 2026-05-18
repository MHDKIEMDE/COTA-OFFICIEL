import { NextResponse, type NextRequest } from "next/server";

const PROTECTED = ["/dashboard", "/predictions", "/coupon"];

export function middleware(request: NextRequest) {
  const isProtected = PROTECTED.some((r) => request.nextUrl.pathname.startsWith(r));
  if (!isProtected) return NextResponse.next();

  const token = request.cookies.get("sanctum_token")?.value;
  if (!token) {
    return NextResponse.redirect(new URL("/login", request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)"],
};
