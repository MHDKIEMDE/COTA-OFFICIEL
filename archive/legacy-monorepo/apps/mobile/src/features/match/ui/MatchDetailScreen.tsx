import {
  Linking,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { useState } from "react";
import { C } from "@/theme/colors";

type DetailTab = "apercu" | "stats" | "compo" | "h2h" | "cotes" | "stream";

const FORM_COLOR: Record<string, string> = {
  W: C.won, D: C.gold, L: C.lost,
};

const EVENT_ICON: Record<string, string> = {
  goal: "⚽", yellow: "🟨", red: "🟥", sub: "🔄", var: "📺",
};

// ─── Bouton Fermer ────────────────────────────────────────────
function CloseBtn({ onPress }: { onPress: () => void }) {
  return (
    <TouchableOpacity onPress={onPress} style={cs.closeBtn} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
      <Text style={cs.closeBtnText}>✕</Text>
    </TouchableOpacity>
  );
}

// ─── Header match ─────────────────────────────────────────────
function MatchHeader({ match }: { match: any }) {
  const isLive = match.status === "live";
  const isFinished = match.status === "finished";
  const hasScore = match.home_score !== null;

  const time = match.match_date
    ? new Date(match.match_date).toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" })
    : "";

  return (
    <View style={cs.header}>
      {/* League */}
      <View style={cs.leagueRow}>
        <Text style={cs.leagueFlag}>{match.league_flag}</Text>
        <Text style={cs.leagueName}>{match.league}</Text>
        {isLive && (
          <View style={cs.livePill}>
            <View style={cs.liveDot} />
            <Text style={cs.liveText}>{match.minute}'</Text>
          </View>
        )}
      </View>

      {/* Score / temps */}
      <View style={cs.scoreRow}>
        {/* Home */}
        <View style={cs.teamCol}>
          <Text style={cs.teamLogo}>{match.home_logo}</Text>
          <Text style={cs.teamName} numberOfLines={2}>{match.home_team}</Text>
        </View>

        {/* Centre */}
        <View style={cs.scoreCenter}>
          {hasScore ? (
            <>
              <Text style={cs.score}>
                {match.home_score} — {match.away_score}
              </Text>
              {isFinished && <Text style={cs.statusLabel}>TERMINÉ</Text>}
            </>
          ) : (
            <>
              <Text style={cs.kickoff}>{time}</Text>
              <Text style={cs.vsLabel}>vs</Text>
            </>
          )}
        </View>

        {/* Away */}
        <View style={[cs.teamCol, { alignItems: "flex-end" }]}>
          <Text style={cs.teamLogo}>{match.away_logo}</Text>
          <Text style={[cs.teamName, { textAlign: "right" }]} numberOfLines={2}>{match.away_team}</Text>
        </View>
      </View>

      {/* Forme */}
      {match.home_form && (
        <View style={cs.formRow}>
          <View style={cs.formSet}>
            {match.home_form.map((r: string, i: number) => (
              <View key={i} style={[cs.formDot, { backgroundColor: FORM_COLOR[r] ?? C.dim }]}>
                <Text style={cs.formLetter}>{r}</Text>
              </View>
            ))}
          </View>
          <Text style={cs.formLabel}>FORME</Text>
          <View style={cs.formSet}>
            {match.away_form.map((r: string, i: number) => (
              <View key={i} style={[cs.formDot, { backgroundColor: FORM_COLOR[r] ?? C.dim }]}>
                <Text style={cs.formLetter}>{r}</Text>
              </View>
            ))}
          </View>
        </View>
      )}

      {/* Infos */}
      <View style={cs.metaRow}>
        {match.venue && <Text style={cs.metaText}>🏟️ {match.venue}</Text>}
        {match.referee && <Text style={cs.metaText}>⚖️ {match.referee}</Text>}
      </View>
    </View>
  );
}

// ─── Événements (live) ───────────────────────────────────────
function EventsTimeline({ events }: { events: any[] }) {
  if (!events?.length) return null;
  const sorted = [...events].sort((a, b) => a.minute - b.minute);
  return (
    <View style={cs.section}>
      <Text style={cs.sectionTitle}>ÉVÉNEMENTS</Text>
      {sorted.map((ev, i) => {
        const isHome = ev.team === "home";
        return (
          <View key={i} style={[cs.eventRow, isHome ? cs.eventHome : cs.eventAway]}>
            {isHome && (
              <View style={cs.eventLeft}>
                <Text style={cs.eventPlayer}>{ev.player}</Text>
                {ev.assist && <Text style={cs.eventAssist}>↳ {ev.assist}</Text>}
              </View>
            )}
            <View style={cs.eventCenter}>
              <Text style={cs.eventMinute}>{ev.minute}'</Text>
              <Text style={cs.eventIcon}>{EVENT_ICON[ev.type] ?? "•"}</Text>
            </View>
            {!isHome && (
              <View style={cs.eventRight}>
                <Text style={cs.eventPlayer}>{ev.player}</Text>
                {ev.assist && <Text style={cs.eventAssist}>↳ {ev.assist}</Text>}
              </View>
            )}
          </View>
        );
      })}
    </View>
  );
}

// ─── Onglet Aperçu ───────────────────────────────────────────
function AperçuTab({ match }: { match: any }) {
  const pred = match.prediction;
  return (
    <View style={{ gap: 0 }}>
      {/* Notre pronostic IA */}
      {pred && (
        <View style={cs.section}>
          <Text style={cs.sectionTitle}>PRONOSTIC IA COTA</Text>
          <View style={cs.predCard}>
            <View style={cs.predTop}>
              <View style={cs.predBadge}>
                <Text style={cs.predBadgeText}>{pred.pick}</Text>
              </View>
              <View style={cs.predOddsPill}>
                <Text style={cs.predOddsText}>x{pred.odds.toFixed(2)}</Text>
              </View>
              <View style={{ flexDirection: "row", gap: 1, marginLeft: "auto" as any }}>
                {[1, 2, 3, 4].map((i) => (
                  <Text key={i} style={{ fontSize: 12, color: i <= pred.confidence ? C.gold : C.dim }}>★</Text>
                ))}
              </View>
            </View>
            <Text style={cs.predAnalysis}>{pred.analysis}</Text>
          </View>
        </View>
      )}

      {/* Absents */}
      {(match.home_absent?.length > 0 || match.away_absent?.length > 0) && (
        <View style={cs.section}>
          <Text style={cs.sectionTitle}>ABSENTS / BLESSÉS</Text>
          <View style={cs.absentGrid}>
            <View style={cs.absentCol}>
              <Text style={cs.absentTeam}>{match.home_team}</Text>
              {(match.home_absent ?? []).map((a: any, i: number) => (
                <View key={i} style={cs.absentRow}>
                  <Text style={cs.absentName}>{a.name}</Text>
                  <Text style={cs.absentReason}>{a.reason}</Text>
                  <Text style={cs.absentReturn}>Retour : {a.return}</Text>
                </View>
              ))}
            </View>
            <View style={cs.absentDivider} />
            <View style={cs.absentCol}>
              <Text style={[cs.absentTeam, { textAlign: "right" }]}>{match.away_team}</Text>
              {(match.away_absent ?? []).map((a: any, i: number) => (
                <View key={i} style={[cs.absentRow, { alignItems: "flex-end" }]}>
                  <Text style={cs.absentName}>{a.name}</Text>
                  <Text style={cs.absentReason}>{a.reason}</Text>
                  <Text style={cs.absentReturn}>Retour : {a.return}</Text>
                </View>
              ))}
            </View>
          </View>
        </View>
      )}

      {/* Events live */}
      {match.events && <EventsTimeline events={match.events} />}
    </View>
  );
}

// ─── Onglet Stats ─────────────────────────────────────────────
function StatsTab({ match }: { match: any }) {
  const stats: any[] = match.stats ?? [];
  if (!stats.length) {
    return (
      <View style={cs.empty}>
        <Text style={cs.emptyText}>Stats disponibles dès le coup d'envoi</Text>
      </View>
    );
  }
  return (
    <View style={cs.section}>
      {stats.map((st, i) => {
        const homePct = st.home_val;
        const awayPct = 100 - homePct;
        return (
          <View key={i} style={cs.statRow}>
            <Text style={cs.statHome}>{st.home}</Text>
            <View style={cs.statBarWrap}>
              <Text style={cs.statLabel}>{st.label}</Text>
              <View style={cs.statBar}>
                <View style={[cs.statBarHome, { flex: homePct }]} />
                <View style={[cs.statBarAway, { flex: awayPct }]} />
              </View>
            </View>
            <Text style={cs.statAway}>{st.away}</Text>
          </View>
        );
      })}
    </View>
  );
}

// ─── Onglet Composition ───────────────────────────────────────
function CompoTab({ match }: { match: any }) {
  const home = match.home_lineup;
  const away = match.away_lineup;
  if (!home) return <View style={cs.empty}><Text style={cs.emptyText}>Compositions non encore annoncées</Text></View>;

  return (
    <View>
      {/* Terrain visuel simple */}
      <View style={cs.pitch}>
        <Text style={cs.pitchFormation}>{home.formation} — {away.formation}</Text>
        <View style={cs.pitchDivider} />
      </View>

      {/* Listes côte à côte */}
      <View style={cs.section}>
        <Text style={cs.sectionTitle}>TITULAIRES</Text>
        <View style={cs.compoGrid}>
          {/* Home */}
          <View style={cs.compoCol}>
            <Text style={cs.compoTeamName}>{match.home_team}</Text>
            {home.players.map((p: any, i: number) => (
              <View key={i} style={cs.playerRow}>
                <View style={cs.playerNum}>
                  <Text style={cs.playerNumText}>{p.number}</Text>
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={cs.playerName} numberOfLines={1}>{p.name}</Text>
                  <Text style={cs.playerPos}>{p.position}</Text>
                </View>
                <View>
                  <Text style={cs.playerValue}>{p.value}</Text>
                  {p.rating && <Text style={[cs.playerRating, { color: p.rating >= 8 ? C.won : p.rating >= 7 ? C.gold : C.textMuted }]}>{p.rating}</Text>}
                </View>
              </View>
            ))}
          </View>

          <View style={cs.compoDivider} />

          {/* Away */}
          <View style={cs.compoCol}>
            <Text style={[cs.compoTeamName, { textAlign: "right" }]}>{match.away_team}</Text>
            {away.players.map((p: any, i: number) => (
              <View key={i} style={[cs.playerRow, { flexDirection: "row-reverse" }]}>
                <View style={cs.playerNum}>
                  <Text style={cs.playerNumText}>{p.number}</Text>
                </View>
                <View style={{ flex: 1, alignItems: "flex-end" }}>
                  <Text style={cs.playerName} numberOfLines={1}>{p.name}</Text>
                  <Text style={cs.playerPos}>{p.position}</Text>
                </View>
                <View style={{ alignItems: "flex-end" }}>
                  <Text style={cs.playerValue}>{p.value}</Text>
                  {p.rating && <Text style={[cs.playerRating, { color: p.rating >= 8 ? C.won : p.rating >= 7 ? C.gold : C.textMuted }]}>{p.rating}</Text>}
                </View>
              </View>
            ))}
          </View>
        </View>
      </View>
    </View>
  );
}

// ─── Onglet H2H ───────────────────────────────────────────────
function H2HTab({ match }: { match: any }) {
  const h2h: any[] = match.h2h ?? [];
  const homeWins = h2h.filter((m) => m.winner === "home").length;
  const awayWins = h2h.filter((m) => m.winner === "away").length;
  const draws = h2h.filter((m) => m.winner === "draw").length;

  return (
    <View>
      {/* Summary */}
      <View style={cs.section}>
        <View style={cs.h2hSummary}>
          <View style={cs.h2hBox}>
            <Text style={[cs.h2hNum, { color: C.won }]}>{homeWins}</Text>
            <Text style={cs.h2hTeam} numberOfLines={1}>{match.home_team}</Text>
          </View>
          <View style={cs.h2hBox}>
            <Text style={[cs.h2hNum, { color: C.textMuted }]}>{draws}</Text>
            <Text style={cs.h2hTeam}>Nuls</Text>
          </View>
          <View style={cs.h2hBox}>
            <Text style={[cs.h2hNum, { color: C.live }]}>{awayWins}</Text>
            <Text style={cs.h2hTeam} numberOfLines={1}>{match.away_team}</Text>
          </View>
        </View>
      </View>

      {/* Matchs */}
      <View style={cs.section}>
        <Text style={cs.sectionTitle}>5 DERNIÈRES CONFRONTATIONS</Text>
        {h2h.map((m, i) => {
          const homeWon = m.winner === "home";
          const awayWon = m.winner === "away";
          return (
            <View key={i} style={cs.h2hRow}>
              <Text style={cs.h2hDate}>
                {new Date(m.date).toLocaleDateString("fr-FR", { day: "numeric", month: "short", year: "2-digit" })}
              </Text>
              <Text style={[cs.h2hName, homeWon && { color: C.textPrimary, fontWeight: "700" }]} numberOfLines={1}>
                {m.home}
              </Text>
              <View style={[cs.h2hScore, m.winner === "draw" && cs.h2hScoreDraw, homeWon && cs.h2hScoreHome, awayWon && cs.h2hScoreAway]}>
                <Text style={cs.h2hScoreText}>{m.score}</Text>
              </View>
              <Text style={[cs.h2hName, { textAlign: "right" }, awayWon && { color: C.textPrimary, fontWeight: "700" }]} numberOfLines={1}>
                {m.away}
              </Text>
            </View>
          );
        })}
      </View>
    </View>
  );
}

// ─── Onglet Cotes ─────────────────────────────────────────────
function CotesTab({ match }: { match: any }) {
  const odds: any[] = match.odds ?? [];
  const best = {
    home: Math.max(...odds.map((o) => o.home)),
    draw: Math.max(...odds.map((o) => o.draw)),
    away: Math.max(...odds.map((o) => o.away)),
  };

  return (
    <View style={cs.section}>
      <Text style={cs.sectionTitle}>COMPARATEUR DE COTES</Text>

      {/* Header */}
      <View style={cs.cotesHeader}>
        <Text style={[cs.cotesCell, { flex: 2 }]}>Bookmaker</Text>
        <Text style={[cs.cotesCell, cs.cotesCellCenter]}>1</Text>
        <Text style={[cs.cotesCell, cs.cotesCellCenter]}>X</Text>
        <Text style={[cs.cotesCell, cs.cotesCellCenter]}>2</Text>
        <Text style={[cs.cotesCell, cs.cotesCellRight]}>Parier</Text>
      </View>

      {odds.map((o, i) => (
        <View key={i} style={cs.cotesRow}>
          <View style={[{ flex: 2 }, cs.cotesBookmakerCell]}>
            <View style={cs.cotesLogo}>
              <Text style={cs.cotesLogoText}>{o.logo}</Text>
            </View>
            <Text style={cs.cotesBookmaker}>{o.bookmaker}</Text>
          </View>
          <Text style={[cs.cotesOdd, cs.cotesCellCenter, o.home === best.home && cs.cotesBest]}>
            {o.home.toFixed(2)}
          </Text>
          <Text style={[cs.cotesOdd, cs.cotesCellCenter, o.draw === best.draw && cs.cotesBest]}>
            {o.draw.toFixed(2)}
          </Text>
          <Text style={[cs.cotesOdd, cs.cotesCellCenter, o.away === best.away && cs.cotesBest]}>
            {o.away.toFixed(2)}
          </Text>
          <TouchableOpacity
            style={cs.cotesBetBtn}
            onPress={() => Linking.openURL(o.link)}
            activeOpacity={0.8}
          >
            <Text style={cs.cotesBetBtnText}>Parier →</Text>
          </TouchableOpacity>
        </View>
      ))}

      {/* Disclaimer */}
      <View style={cs.responsibleRow}>
        <Text style={cs.responsibleIcon}>⚠️</Text>
        <Text style={cs.responsibleText}>
          Jeu responsable · 18+ · Pariez uniquement ce que vous pouvez vous permettre de perdre.
        </Text>
      </View>
    </View>
  );
}

// ─── Onglet Stream ────────────────────────────────────────────
const STREAM_SOURCES = [
  { name: "beIN Sports", icon: "📺", type: "TV", note: "Chaîne officielle", free: false, link: "https://www.beinsports.com" },
  { name: "Canal+", icon: "📡", type: "TV", note: "Droits Ligue 1 / CL", free: false, link: "https://www.canalplus.com" },
  { name: "FIFA+", icon: "🌍", type: "Gratuit", note: "Certains matchs en accès libre", free: true, link: "https://www.fifa.com/fifaplus" },
  { name: "YouTube Football", icon: "▶️", type: "Gratuit", note: "Highlights officiels", free: true, link: "https://www.youtube.com/@PremierLeague" },
  { name: "L'Équipe Live", icon: "📰", type: "Gratuit", note: "Certains matchs & résumés", free: true, link: "https://www.lequipe.fr/Multisports/live.html" },
];

function StreamTab() {
  return (
    <View>
      <View style={cs.section}>
        <Text style={cs.sectionTitle}>OÙ REGARDER CE MATCH</Text>

        {/* Avertissement légal */}
        <View style={cs.streamLegal}>
          <Text style={cs.streamLegalIcon}>⚠️</Text>
          <Text style={cs.streamLegalText}>
            COTA ne fournit aucun lien de streaming illégal. Nous référençons uniquement des sources officielles et légales.
          </Text>
        </View>

        {STREAM_SOURCES.map((src, i) => (
          <TouchableOpacity
            key={i}
            style={cs.streamRow}
            onPress={() => Linking.openURL(src.link)}
            activeOpacity={0.8}
          >
            <Text style={cs.streamIcon}>{src.icon}</Text>
            <View style={cs.streamBody}>
              <View style={cs.streamTop}>
                <Text style={cs.streamName}>{src.name}</Text>
                <View style={[cs.streamBadge, src.free ? cs.streamBadgeFree : cs.streamBadgePaid]}>
                  <Text style={[cs.streamBadgeText, src.free ? cs.streamBadgeTextFree : cs.streamBadgeTextPaid]}>
                    {src.free ? "GRATUIT" : "ABONNEMENT"}
                  </Text>
                </View>
              </View>
              <Text style={cs.streamNote}>{src.note}</Text>
            </View>
            <Text style={cs.streamArrow}>›</Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Jeu responsable */}
      <View style={cs.responsibleRow}>
        <Text style={cs.responsibleIcon}>🛡️</Text>
        <Text style={cs.responsibleText}>
          Le visionnage de matchs peut inciter à parier. Pariez de manière responsable. Aide : joueurs-info-service.fr · 09 74 75 13 13
        </Text>
      </View>
    </View>
  );
}

// ─── Écran principal ──────────────────────────────────────────
export default function MatchDetailScreen({
  match,
  visible,
  onClose,
}: {
  match: any;
  visible: boolean;
  onClose: () => void;
}) {
  const [activeTab, setActiveTab] = useState<DetailTab>("apercu");

  const TABS: { key: DetailTab; label: string }[] = [
    { key: "apercu",  label: "Aperçu" },
    { key: "cotes",   label: "Cotes" },
    { key: "stats",   label: "Stats" },
    { key: "compo",   label: "Compo" },
    { key: "h2h",     label: "H2H" },
    { key: "stream",  label: "📺 Stream" },
  ];

  return (
    <Modal visible={visible} animationType="slide" presentationStyle="pageSheet" onRequestClose={onClose}>
      <View style={cs.root}>
        {/* Close */}
        <View style={cs.topBar}>
          <View style={cs.topBarHandle} />
          <CloseBtn onPress={onClose} />
        </View>

        {/* Header match */}
        <MatchHeader match={match} />

        {/* Tabs */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={cs.tabsScroll}
          contentContainerStyle={cs.tabsContent}
        >
          {TABS.map((t) => (
            <TouchableOpacity
              key={t.key}
              style={[cs.tab, activeTab === t.key && cs.tabActive]}
              onPress={() => setActiveTab(t.key)}
            >
              <Text style={[cs.tabText, activeTab === t.key && cs.tabTextActive]}>
                {t.label}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {/* Content */}
        <ScrollView
          style={cs.body}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingBottom: 48 }}
        >
          {activeTab === "apercu" && <AperçuTab match={match} />}
          {activeTab === "stats"  && <StatsTab match={match} />}
          {activeTab === "compo"  && <CompoTab match={match} />}
          {activeTab === "h2h"    && <H2HTab match={match} />}
          {activeTab === "cotes"  && <CotesTab match={match} />}
          {activeTab === "stream" && <StreamTab />}
        </ScrollView>
      </View>
    </Modal>
  );
}

// ─── Styles ───────────────────────────────────────────────────
const cs = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },

  // Top bar
  topBar: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    paddingTop: 12,
    paddingBottom: 4,
    paddingHorizontal: 16,
  },
  topBarHandle: { width: 36, height: 4, borderRadius: 2, backgroundColor: C.dim },
  closeBtn: {
    position: "absolute",
    right: 16,
    top: 10,
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: C.bg3,
    alignItems: "center",
    justifyContent: "center",
  },
  closeBtnText: { color: C.textMuted, fontSize: 16, lineHeight: 20 },

  // Header
  header: {
    backgroundColor: C.bg2,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingBottom: 12,
  },
  leagueRow: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 6,
    paddingTop: 10,
    paddingBottom: 8,
  },
  leagueFlag: { fontSize: 14 },
  leagueName: { color: C.textMuted, fontSize: 12, fontWeight: "600" },
  livePill: {
    flexDirection: "row",
    alignItems: "center",
    gap: 4,
    backgroundColor: `${C.live}22`,
    borderWidth: 1,
    borderColor: `${C.live}44`,
    borderRadius: 10,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  liveDot: { width: 5, height: 5, borderRadius: 3, backgroundColor: C.live },
  liveText: { color: C.live, fontSize: 10, fontWeight: "800" },
  scoreRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 20,
    paddingVertical: 8,
    gap: 8,
  },
  teamCol: { flex: 1, alignItems: "center", gap: 6 },
  teamLogo: { fontSize: 36 },
  teamName: { color: C.textPrimary, fontSize: 13, fontWeight: "700", textAlign: "center" },
  scoreCenter: { alignItems: "center", gap: 4, minWidth: 80 },
  score: { color: C.textPrimary, fontSize: 32, fontWeight: "900", fontVariant: ["tabular-nums"] },
  statusLabel: { color: C.textMuted, fontSize: 10, fontWeight: "700", letterSpacing: 1 },
  kickoff: { color: C.primary, fontSize: 22, fontWeight: "800" },
  vsLabel: { color: C.textMuted, fontSize: 12 },
  formRow: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 12,
    paddingHorizontal: 20,
    paddingBottom: 8,
  },
  formSet: { flexDirection: "row", gap: 4 },
  formLabel: { color: C.textMuted, fontSize: 10, fontWeight: "700", letterSpacing: 1 },
  formDot: {
    width: 20,
    height: 20,
    borderRadius: 4,
    alignItems: "center",
    justifyContent: "center",
  },
  formLetter: { color: "#fff", fontSize: 9, fontWeight: "900" },
  metaRow: {
    flexDirection: "row",
    justifyContent: "center",
    gap: 16,
    paddingHorizontal: 20,
    paddingTop: 4,
  },
  metaText: { color: C.textMuted, fontSize: 10 },

  // Tabs
  tabsScroll: {
    backgroundColor: C.bg2,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    flexGrow: 0,
  },
  tabsContent: { paddingHorizontal: 8, gap: 0 },
  tab: {
    paddingHorizontal: 14,
    paddingVertical: 11,
    borderBottomWidth: 2,
    borderBottomColor: "transparent",
  },
  tabActive: { borderBottomColor: C.primary },
  tabText: { color: C.textMuted, fontSize: 13, fontWeight: "600" },
  tabTextActive: { color: C.primary, fontWeight: "700" },

  body: { flex: 1 },

  // Sections
  section: { paddingVertical: 12 },
  sectionTitle: {
    color: C.textMuted,
    fontSize: 10,
    fontWeight: "800",
    letterSpacing: 1.5,
    paddingHorizontal: 16,
    paddingBottom: 8,
  },

  // Empty
  empty: { padding: 32, alignItems: "center" },
  emptyText: { color: C.textMuted, fontSize: 13 },

  // Pronostic IA
  predCard: {
    marginHorizontal: 12,
    backgroundColor: C.bg3,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: `${C.primary}33`,
    padding: 14,
    gap: 10,
  },
  predTop: { flexDirection: "row", alignItems: "center", gap: 8 },
  predBadge: {
    backgroundColor: `${C.primary}22`,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    borderRadius: 6,
    paddingHorizontal: 12,
    paddingVertical: 5,
  },
  predBadgeText: { color: C.primaryLight, fontSize: 14, fontWeight: "900" },
  predOddsPill: {
    backgroundColor: `${C.accent}18`,
    borderWidth: 1,
    borderColor: `${C.accent}44`,
    borderRadius: 6,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  predOddsText: { color: C.accent, fontSize: 13, fontWeight: "800" },
  predAnalysis: { color: C.textSecondary, fontSize: 13, lineHeight: 20 },

  // Absents
  absentGrid: {
    flexDirection: "row",
    paddingHorizontal: 12,
    gap: 0,
  },
  absentCol: { flex: 1, gap: 10 },
  absentDivider: { width: 1, backgroundColor: C.divider, marginHorizontal: 12 },
  absentTeam: { color: C.textMuted, fontSize: 10, fontWeight: "800", letterSpacing: 0.8 },
  absentRow: { gap: 2 },
  absentName: { color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  absentReason: { color: C.live, fontSize: 11 },
  absentReturn: { color: C.textMuted, fontSize: 10 },

  // Events timeline
  eventRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  eventHome: { justifyContent: "flex-start" },
  eventAway: { flexDirection: "row-reverse" },
  eventLeft: { flex: 1, alignItems: "flex-start" },
  eventRight: { flex: 1, alignItems: "flex-end" },
  eventCenter: { alignItems: "center", minWidth: 50, gap: 2 },
  eventMinute: { color: C.textMuted, fontSize: 11, fontWeight: "700" },
  eventIcon: { fontSize: 16 },
  eventPlayer: { color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  eventAssist: { color: C.textMuted, fontSize: 11 },

  // Stats
  statRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    gap: 10,
  },
  statHome: { width: 36, color: C.textPrimary, fontSize: 13, fontWeight: "700", textAlign: "right" },
  statAway: { width: 36, color: C.textPrimary, fontSize: 13, fontWeight: "700", textAlign: "left" },
  statBarWrap: { flex: 1, gap: 4, alignItems: "center" },
  statLabel: { color: C.textMuted, fontSize: 10, fontWeight: "600" },
  statBar: { flexDirection: "row", width: "100%", height: 5, borderRadius: 3, overflow: "hidden" },
  statBarHome: { backgroundColor: C.primary, borderRadius: 3 },
  statBarAway: { backgroundColor: C.live, borderRadius: 3 },

  // H2H
  h2hSummary: {
    flexDirection: "row",
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingBottom: 16,
  },
  h2hBox: { flex: 1, alignItems: "center", gap: 4 },
  h2hNum: { fontSize: 32, fontWeight: "900" },
  h2hTeam: { color: C.textMuted, fontSize: 11, fontWeight: "600", textAlign: "center" },
  h2hRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    gap: 6,
  },
  h2hDate: { color: C.textMuted, fontSize: 10, minWidth: 52 },
  h2hName: { flex: 1, color: C.textSecondary, fontSize: 12 },
  h2hScore: {
    backgroundColor: C.bg3,
    borderRadius: 4,
    paddingHorizontal: 10,
    paddingVertical: 3,
    minWidth: 68,
    alignItems: "center",
  },
  h2hScoreDraw: { backgroundColor: `${C.gold}18` },
  h2hScoreHome: { backgroundColor: `${C.won}18` },
  h2hScoreAway: { backgroundColor: `${C.live}18` },
  h2hScoreText: { color: C.textPrimary, fontSize: 12, fontWeight: "800", fontVariant: ["tabular-nums"] },

  // Compo
  pitch: {
    backgroundColor: "#0d2b0d",
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: "#1a3d1a",
    alignItems: "center",
    gap: 6,
  },
  pitchFormation: { color: "#4ade80", fontSize: 12, fontWeight: "700", letterSpacing: 1 },
  pitchDivider: { width: "60%", height: 1, backgroundColor: "#ffffff22" },
  compoGrid: { flexDirection: "row", paddingHorizontal: 8 },
  compoCol: { flex: 1, gap: 2 },
  compoDivider: { width: 1, backgroundColor: C.divider, marginHorizontal: 6 },
  compoTeamName: { color: C.textMuted, fontSize: 10, fontWeight: "800", letterSpacing: 0.8, paddingHorizontal: 4, paddingBottom: 8 },
  playerRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    paddingVertical: 7,
    paddingHorizontal: 4,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  playerNum: {
    width: 22,
    height: 22,
    borderRadius: 4,
    backgroundColor: C.bg3,
    alignItems: "center",
    justifyContent: "center",
  },
  playerNumText: { color: C.textMuted, fontSize: 10, fontWeight: "700" },
  playerName: { color: C.textPrimary, fontSize: 12, fontWeight: "700" },
  playerPos: { color: C.textMuted, fontSize: 9 },
  playerValue: { color: C.accent, fontSize: 9, fontWeight: "700", textAlign: "right" },
  playerRating: { fontSize: 11, fontWeight: "800", textAlign: "right" },

  // Cotes
  cotesHeader: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: C.bg3,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  cotesCell: { flex: 1, color: C.textMuted, fontSize: 11, fontWeight: "700" },
  cotesCellCenter: { textAlign: "center" },
  cotesCellRight: { textAlign: "right" },
  cotesRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  cotesBookmakerCell: { flexDirection: "row", alignItems: "center", gap: 8 },
  cotesLogo: {
    width: 26,
    height: 26,
    borderRadius: 6,
    backgroundColor: C.bg3,
    alignItems: "center",
    justifyContent: "center",
  },
  cotesLogoText: { color: C.primary, fontSize: 8, fontWeight: "800" },
  cotesBookmaker: { color: C.textSecondary, fontSize: 12, fontWeight: "600" },
  cotesOdd: { flex: 1, color: C.textPrimary, fontSize: 14, fontWeight: "700" },
  cotesBest: { color: C.accent, fontWeight: "900" },
  cotesBetBtn: {
    backgroundColor: C.primary,
    borderRadius: 6,
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  cotesBetBtnText: { color: "#fff", fontSize: 11, fontWeight: "800" },
  responsibleRow: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 8,
    margin: 12,
    backgroundColor: `${C.gold}12`,
    borderWidth: 1,
    borderColor: `${C.gold}22`,
    borderRadius: 8,
    padding: 12,
  },
  responsibleIcon: { fontSize: 14 },
  responsibleText: { flex: 1, color: C.gold, fontSize: 11, lineHeight: 17 },

  // Stream
  streamLegal: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 8,
    marginHorizontal: 12,
    marginBottom: 12,
    backgroundColor: `${C.primary}10`,
    borderWidth: 1,
    borderColor: `${C.primary}22`,
    borderRadius: 8,
    padding: 12,
  },
  streamLegalIcon: { fontSize: 14 },
  streamLegalText: { flex: 1, color: C.textMuted, fontSize: 11, lineHeight: 17 },
  streamRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 12,
    paddingVertical: 13,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    gap: 12,
  },
  streamIcon: { fontSize: 22 },
  streamBody: { flex: 1, gap: 3 },
  streamTop: { flexDirection: "row", alignItems: "center", gap: 8 },
  streamName: { color: C.textPrimary, fontSize: 14, fontWeight: "700" },
  streamBadge: { borderRadius: 4, paddingHorizontal: 7, paddingVertical: 2 },
  streamBadgeFree: { backgroundColor: `${C.accent}20`, borderWidth: 1, borderColor: `${C.accent}44` },
  streamBadgePaid: { backgroundColor: `${C.gold}18`, borderWidth: 1, borderColor: `${C.gold}33` },
  streamBadgeText: { fontSize: 9, fontWeight: "800", letterSpacing: 0.5 },
  streamBadgeTextFree: { color: C.accent },
  streamBadgeTextPaid: { color: C.gold },
  streamNote: { color: C.textMuted, fontSize: 12 },
  streamArrow: { color: C.dim, fontSize: 20 },
});
