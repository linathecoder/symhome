from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.units import cm
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    HRFlowable, PageBreak, KeepTogether
)
from reportlab.lib.enums import TA_LEFT, TA_CENTER, TA_JUSTIFY

W, H = A4
MARGIN = 1.8 * cm

BLUE_DARK  = colors.HexColor("#1F497D")
BLUE_MED   = colors.HexColor("#2E75B6")
BLUE_LIGHT = colors.HexColor("#D0E4F7")
RED        = colors.HexColor("#C0392B")
GREEN      = colors.HexColor("#1E6B2E")
GREY_BG    = colors.HexColor("#F5F5F5")
GREY_LINE  = colors.HexColor("#AAAAAA")
WHITE      = colors.white

styles = getSampleStyleSheet()

def style(name, **kw):
    s = ParagraphStyle(name, **kw)
    return s

S_TITLE   = style("S_TITLE",   fontSize=20, leading=26, textColor=BLUE_DARK,  fontName="Helvetica-Bold", alignment=TA_CENTER, spaceAfter=4)
S_SUBTITLE= style("S_SUBTITLE",fontSize=13, leading=18, textColor=BLUE_MED,   fontName="Helvetica-Bold", alignment=TA_CENTER, spaceAfter=2)
S_META    = style("S_META",    fontSize=10, leading=14, textColor=colors.HexColor("#555555"), fontName="Helvetica", alignment=TA_CENTER, spaceAfter=2)
S_EX_HEAD = style("S_EX_HEAD", fontSize=13, leading=18, textColor=WHITE,       fontName="Helvetica-Bold", alignment=TA_LEFT)
S_PART    = style("S_PART",    fontSize=11, leading=15, textColor=BLUE_DARK,   fontName="Helvetica-Bold", spaceAfter=4, spaceBefore=8)
S_Q       = style("S_Q",       fontSize=10, leading=14, textColor=colors.black, fontName="Helvetica-Bold", spaceAfter=2, spaceBefore=4)
S_BODY    = style("S_BODY",    fontSize=10, leading=14, textColor=colors.black, fontName="Helvetica", spaceAfter=2, alignment=TA_JUSTIFY)
S_CODE    = style("S_CODE",    fontSize=8.5, leading=12, textColor=colors.HexColor("#1A1A1A"),
                   fontName="Courier", backColor=GREY_BG, borderPadding=(4,6,4,6),
                   spaceAfter=4, spaceBefore=2)
S_NOTE    = style("S_NOTE",    fontSize=9, leading=13, textColor=GREEN, fontName="Helvetica-Oblique", spaceAfter=2)
S_PTS     = style("S_PTS",     fontSize=9, leading=12, textColor=RED, fontName="Helvetica-Bold", alignment=TA_LEFT)

def sp(n=0.3):   return Spacer(1, n*cm)
def hr():        return HRFlowable(width="100%", thickness=1, color=GREY_LINE, spaceAfter=4, spaceBefore=4)
def hr_blue(t=2):return HRFlowable(width="100%", thickness=t, color=BLUE_DARK, spaceAfter=6, spaceBefore=6)

def ex_header(num, title, pts):
    label = f"  Exercice {num} — {title}   ({pts} pts)"
    t = Table([[Paragraph(label, S_EX_HEAD)]], colWidths=[W - 2*MARGIN])
    t.setStyle(TableStyle([
        ("BACKGROUND", (0,0), (-1,-1), BLUE_DARK),
        ("TOPPADDING",  (0,0), (-1,-1), 7),
        ("BOTTOMPADDING",(0,0),(-1,-1), 7),
        ("LEFTPADDING", (0,0), (-1,-1), 10),
        ("ROUNDEDCORNERS", [4]),
    ]))
    return t

def q(num_str, text): return Paragraph(f"<b>Q{num_str}.</b> {text}", S_Q)
def body(text):        return Paragraph(text, S_BODY)
def code(text):        return Paragraph(text, S_CODE)
def note(text):        return Paragraph(f"&#x2714; {text}", S_NOTE)
def pts(text):         return Paragraph(text, S_PTS)

def code_block(lines):
    """Multi-line code as a grey table"""
    text = "<br/>".join(lines)
    t = Table([[Paragraph(text, S_CODE)]], colWidths=[W - 2*MARGIN])
    t.setStyle(TableStyle([
        ("BACKGROUND", (0,0), (-1,-1), GREY_BG),
        ("BOX",        (0,0), (-1,-1), 0.5, GREY_LINE),
        ("TOPPADDING",  (0,0), (-1,-1), 6),
        ("BOTTOMPADDING",(0,0),(-1,-1), 6),
        ("LEFTPADDING", (0,0), (-1,-1), 10),
    ]))
    return t

def grid_table(headers, rows, col_widths):
    data = [[Paragraph(h, style("th", fontSize=9, fontName="Helvetica-Bold",
                                 textColor=WHITE, alignment=TA_CENTER)) for h in headers]]
    for row in rows:
        data.append([Paragraph(str(c), style("td", fontSize=9, fontName="Courier",
                                              textColor=colors.black)) for c in row])
    t = Table(data, colWidths=col_widths)
    t.setStyle(TableStyle([
        ("BACKGROUND", (0,0), (-1,0), BLUE_MED),
        ("BACKGROUND", (0,1), (0,-1), BLUE_LIGHT),
        ("ROWBACKGROUNDS", (1,1), (-1,-1), [WHITE, GREY_BG]),
        ("BOX",   (0,0), (-1,-1), 0.5, GREY_LINE),
        ("INNERGRID",(0,0),(-1,-1), 0.3, GREY_LINE),
        ("TOPPADDING",(0,0),(-1,-1), 4),
        ("BOTTOMPADDING",(0,0),(-1,-1), 4),
        ("LEFTPADDING",(0,0),(-1,-1), 6),
        ("VALIGN",(0,0),(-1,-1),"MIDDLE"),
    ]))
    return t

# ──────────────────────────────────────────────────────────────────────────────
story = []

# ── Page de garde ─────────────────────────────────────────────────────────────
story += [
    sp(1),
    Paragraph("CORRECTION — EXAMEN DE TRAVAUX PRATIQUES", S_TITLE),
    Paragraph("Administration de Base de Données — Oracle", S_SUBTITLE),
    Paragraph("Atelier 1 · Atelier 2 · Atelier 3 · Atelier 4", S_META),
    Paragraph("Année Universitaire 2025-2026", S_META),
    sp(0.4),
    hr_blue(3),
    sp(0.2),
]

info_data = [
    ["Durée", "2 heures"],
    ["Barème total", "20 points (+ 2 pts bonus)"],
    ["Document", "Correction officielle — usage enseignant"],
]
info_t = Table(
    [[Paragraph(r[0], style("ik", fontSize=10, fontName="Helvetica-Bold", textColor=BLUE_DARK)),
      Paragraph(r[1], style("iv", fontSize=10, fontName="Helvetica"))] for r in info_data],
    colWidths=[5*cm, W - 2*MARGIN - 5*cm]
)
info_t.setStyle(TableStyle([
    ("BACKGROUND", (0,0), (0,-1), BLUE_LIGHT),
    ("BOX",   (0,0), (-1,-1), 0.5, GREY_LINE),
    ("INNERGRID",(0,0),(-1,-1), 0.3, GREY_LINE),
    ("TOPPADDING",(0,0),(-1,-1), 5),
    ("BOTTOMPADDING",(0,0),(-1,-1), 5),
    ("LEFTPADDING",(0,0),(-1,-1), 8),
]))
story += [info_t, sp(0.5)]

# ══════════════════════════════════════════════════════════════════════════════
# EXERCICE 1 — Dictionnaire de données  (5 pts)
# ══════════════════════════════════════════════════════════════════════════════
story += [ex_header(1, "Dictionnaire de données", 5), sp(0.3)]

story += [
    q(1, "Nombre total de vues dans le dictionnaire"),
    code_block(["SELECT COUNT(*) FROM DICTIONARY;"]),
    note("La vue DICTIONARY (ou DICT) liste toutes les vues du dictionnaire avec leur description."),
    sp(0.2),

    q(2, "Vues contenant la chaîne 'TABLE'"),
    code_block(["SELECT TABLE_NAME FROM DICTIONARY",
                "WHERE TABLE_NAME LIKE '%TABLE%';"]),
    sp(0.2),

    q(3, "Vues possédant une colonne nommée 'OWNER'"),
    code_block(["SELECT TABLE_NAME FROM DICT_COLUMNS",
                "WHERE COLUMN_NAME = 'OWNER';"]),
    sp(0.2),

    q("4a", "Liste des vues du compte HR"),
    code_block(["-- Connecté en HR",
                "SELECT VIEW_NAME, TEXT_LENGTH",
                "FROM USER_VIEWS;"]),
    sp(0.2),

    q("4b", "Contraintes du compte HR avec type et statut"),
    code_block(["SELECT CONSTRAINT_NAME, TABLE_NAME, CONSTRAINT_TYPE,",
                "       SEARCH_CONDITION, STATUS",
                "FROM USER_CONSTRAINTS",
                "ORDER BY TABLE_NAME, CONSTRAINT_TYPE;"]),
    sp(0.2),

    q(5, "Procédure PS_GET_INFO_ABOUT_USER"),
    code_block([
        "CREATE OR REPLACE PROCEDURE PS_GET_INFO_ABOUT_USER (p_user IN VARCHAR2) IS",
        "BEGIN",
        "  -- Informations générales",
        "  FOR r IN (SELECT USERNAME, CREATED, PROFILE,",
        "                   DEFAULT_TABLESPACE, TEMPORARY_TABLESPACE, ACCOUNT_STATUS",
        "            FROM DBA_USERS WHERE USERNAME = UPPER(p_user)) LOOP",
        "    DBMS_OUTPUT.PUT_LINE('=== Infos compte : ' || r.USERNAME || ' ===');",
        "    DBMS_OUTPUT.PUT_LINE('Créé le       : ' || r.CREATED);",
        "    DBMS_OUTPUT.PUT_LINE('Profil        : ' || r.PROFILE);",
        "    DBMS_OUTPUT.PUT_LINE('TS défaut     : ' || r.DEFAULT_TABLESPACE);",
        "    DBMS_OUTPUT.PUT_LINE('TS temporaire : ' || r.TEMPORARY_TABLESPACE);",
        "    DBMS_OUTPUT.PUT_LINE('Statut        : ' || r.ACCOUNT_STATUS);",
        "  END LOOP;",
        "  -- Liste des objets",
        "  DBMS_OUTPUT.PUT_LINE('--- Objets du schéma ---');",
        "  FOR o IN (SELECT OBJECT_NAME, OBJECT_TYPE, CREATED, LAST_DDL_TIME",
        "            FROM DBA_OBJECTS WHERE OWNER = UPPER(p_user)",
        "            ORDER BY OBJECT_TYPE, OBJECT_NAME) LOOP",
        "    DBMS_OUTPUT.PUT_LINE(o.OBJECT_TYPE || ' : ' || o.OBJECT_NAME);",
        "  END LOOP;",
        "END;",
        "/",
        "-- Exécution :",
        "EXEC PS_GET_INFO_ABOUT_USER('HR');",
    ]),
    note("Barème : Q1=0.5 · Q2=0.5 · Q3=0.5 · Q4=1 · Q5=2.5 pts"),
    sp(0.4),
]

# ══════════════════════════════════════════════════════════════════════════════
# EXERCICE 2 — Tablespaces  (5 pts)
# ══════════════════════════════════════════════════════════════════════════════
story += [hr_blue(), ex_header(2, "Structures de stockage (Tablespaces)", 5), sp(0.3)]

story += [
    q(1, "Liste des tablespaces et leur statut"),
    code_block(["SELECT TABLESPACE_NAME, STATUS, CONTENTS, LOGGING",
                "FROM DBA_TABLESPACES",
                "ORDER BY TABLESPACE_NAME;"]),
    sp(0.2),

    q(2, "Création du tablespace TBL_EXAM"),
    code_block([
        "CREATE TABLESPACE TBL_EXAM",
        "  DATAFILE 'C:\\oracle\\oradata\\exam_01.dbf' SIZE 10M,",
        "           'C:\\oracle\\oradata\\exam_02.dbf' SIZE 5M",
        "             AUTOEXTEND ON NEXT 2M MAXSIZE 20M;",
    ]),
    sp(0.2),

    q(3, "Ajout du fichier exam_03.dbf"),
    code_block([
        "ALTER TABLESPACE TBL_EXAM",
        "  ADD DATAFILE 'C:\\oracle\\oradata\\exam_03.dbf'",
        "  SIZE 3M AUTOEXTEND ON NEXT 1M MAXSIZE 8M;",
    ]),
    sp(0.2),

    q(4, "Création de la table Etudiant et insertion de 50 lignes"),
    code_block([
        "-- Création",
        "CREATE TABLE Etudiant (",
        "  num_etud     NUMBER,",
        "  nom_etud     VARCHAR2(50),",
        "  moyenne_etud NUMBER(4,2)",
        ") TABLESPACE TBL_EXAM;",
        "",
        "-- Insertion via bloc PL/SQL",
        "BEGIN",
        "  FOR i IN 1..50 LOOP",
        "    INSERT INTO Etudiant VALUES (i, 'Etudiant ' || i, ROUND(DBMS_RANDOM.VALUE(8,20), 2));",
        "  END LOOP;",
        "  COMMIT;",
        "END;",
        "/",
    ]),
    sp(0.2),

    q(5, "Taille occupée par la table Etudiant (DBA_EXTENTS)"),
    code_block([
        "SELECT SEGMENT_NAME, TABLESPACE_NAME,",
        "       SUM(BYTES)/1024 AS taille_ko",
        "FROM DBA_EXTENTS",
        "WHERE SEGMENT_NAME = 'ETUDIANT'",
        "  AND OWNER = USER",
        "GROUP BY SEGMENT_NAME, TABLESPACE_NAME;",
    ]),
    sp(0.2),

    q(6, "Taille et espace occupé par tablespace"),
    code_block([
        "SELECT df.TABLESPACE_NAME,",
        "       ROUND(SUM(df.BYTES)/1048576, 2)  AS taille_totale_mo,",
        "       ROUND(SUM(ex.total_bytes)/1048576, 2) AS espace_occupe_mo",
        "FROM DBA_DATA_FILES df",
        "LEFT JOIN (",
        "    SELECT TABLESPACE_NAME, SUM(BYTES) AS total_bytes",
        "    FROM DBA_EXTENTS GROUP BY TABLESPACE_NAME",
        ") ex ON df.TABLESPACE_NAME = ex.TABLESPACE_NAME",
        "GROUP BY df.TABLESPACE_NAME",
        "ORDER BY df.TABLESPACE_NAME;",
    ]),
    note("Barème : Q1=0.5 · Q2=1 · Q3=0.5 · Q4=1.5 · Q5=0.75 · Q6=0.75 pts"),
    sp(0.4),
]

# ══════════════════════════════════════════════════════════════════════════════
# EXERCICE 3 — Démarrage / Arrêt / Paramètres  (4 pts)
# ══════════════════════════════════════════════════════════════════════════════
story += [hr_blue(), ex_header(3, "Démarrage, Arrêt et Paramètres d'initialisation", 4), sp(0.3)]

story += [
    q(1, "Modes d'ouverture et vues accessibles"),
    grid_table(
        ["Mode", "V$INSTANCE", "V$DATABASE", "DBA_USERS"],
        [
            ["NOMOUNT", "OUI", "NON", "NON"],
            ["MOUNT",   "OUI", "OUI", "NON"],
            ["OPEN",    "OUI", "OUI", "OUI"],
        ],
        [3.5*cm, 3.5*cm, 3.5*cm, 3.5*cm]
    ),
    body("En mode NOMOUNT, seule l'instance est démarrée (mémoire SGA + processus). "
         "En mode MOUNT, les fichiers de contrôle sont lus. "
         "En mode OPEN, tous les fichiers de données et de journalisation sont accessibles."),
    sp(0.3),

    q(2, "Mode RESTRICTED SESSION — activation / désactivation"),
    code_block([
        "-- Activer le mode restreint",
        "ALTER SYSTEM ENABLE RESTRICTED SESSION;",
        "",
        "-- Seuls les utilisateurs avec le privilège RESTRICTED SESSION peuvent se connecter",
        "-- Accorder ce privilège :",
        "GRANT RESTRICTED SESSION TO HR;",
        "",
        "-- Désactiver le mode restreint",
        "ALTER SYSTEM DISABLE RESTRICTED SESSION;",
    ]),
    note("Effet : les connexions sans le privilège RESTRICTED SESSION reçoivent l'erreur ORA-01035."),
    sp(0.3),

    q(3, "Tableau des paramètres dynamiques/statiques"),
    grid_table(
        ["Paramètre", "Valeur courante (ex.)", "ISSYS_MODIFIABLE", "ISSES_MODIFIABLE", "SCOPE possible"],
        [
            ["JOB_QUEUE_PROCESSES", "10", "IMMEDIATE", "FALSE", "MEMORY / SPFILE / BOTH"],
            ["AUDIT_FILE_DEST",     "...", "DEFERRED", "FALSE", "SPFILE"],
            ["SESSION_MAX_OPEN_FILES","10","FALSE",    "FALSE", "SPFILE (statique)"],
        ],
        [4.5*cm, 3*cm, 3.2*cm, 3.3*cm, 3.5*cm]
    ),
    code_block([
        "-- Requête pour remplir le tableau :",
        "SELECT NAME, VALUE, ISSYS_MODIFIABLE, ISSES_MODIFIABLE",
        "FROM V$PARAMETER",
        "WHERE NAME IN ('job_queue_processes','audit_file_dest','session_max_open_files');",
    ]),
    sp(0.3),

    q(4, "PFILE vs SPFILE"),
    body("<b>PFILE (init&lt;SID&gt;.ora)</b> : fichier texte éditable manuellement. "
         "Utilisé pour des modifications hors ligne ou pour récupérer une configuration corrompue."),
    body("<b>SPFILE (spfile&lt;SID&gt;.ora)</b> : fichier binaire géré par Oracle. "
         "Permet de persister les modifications dynamiques (ALTER SYSTEM ... SCOPE=SPFILE|BOTH) "
         "sans arrêter la base. Recommandé en production."),
    code_block([
        "-- Créer un PFILE à partir du SPFILE courant :",
        "CREATE PFILE FROM SPFILE;",
        "",
        "-- Démarrer depuis le PFILE :",
        "STARTUP PFILE='C:\\oracle\\product\\...\\dbs\\initORCL.ora';",
        "",
        "-- Recréer le SPFILE depuis le PFILE :",
        "CREATE SPFILE FROM PFILE;",
    ]),
    note("Barème : Q1=1 · Q2=1 · Q3=1 · Q4=1 pt"),
    sp(0.4),
]

# ══════════════════════════════════════════════════════════════════════════════
# EXERCICE 4 — Droits d'accès  (6 pts)
# ══════════════════════════════════════════════════════════════════════════════
story += [hr_blue(), ex_header(4, "Gestion des droits d'accès", 6), sp(0.3)]

story += [
    Paragraph("Partie A — Utilisateurs et privilèges (3 pts)", S_PART),

    q(1, "Création de EXAM_USER"),
    code_block([
        "CREATE USER EXAM_USER IDENTIFIED BY Exam2026",
        "  DEFAULT TABLESPACE USERS",
        "  TEMPORARY TABLESPACE TEMP",
        "  QUOTA 10M ON USERS",
        "  QUOTA UNLIMITED ON TEMP;",
    ]),
    sp(0.2),

    q(2, "Privilèges de base"),
    code_block([
        "GRANT CREATE SESSION TO EXAM_USER;",
        "GRANT CREATE TABLE  TO EXAM_USER;",
        "GRANT CREATE VIEW   TO EXAM_USER;",
        "GRANT CREATE SEQUENCE TO EXAM_USER;",
    ]),
    sp(0.2),

    q(3, "Accès à EMPLOYEES du schéma HR avec GRANT OPTION"),
    code_block([
        "GRANT SELECT, INSERT ON HR.EMPLOYEES TO EXAM_USER WITH GRANT OPTION;",
    ]),
    sp(0.2),

    q(4, "Vérification des privilèges"),
    code_block([
        "-- Privilèges système",
        "SELECT PRIVILEGE FROM DBA_SYS_PRIVS WHERE GRANTEE = 'EXAM_USER';",
        "",
        "-- Privilèges objet",
        "SELECT TABLE_NAME, PRIVILEGE, GRANTABLE",
        "FROM DBA_TAB_PRIVS WHERE GRANTEE = 'EXAM_USER';",
    ]),
    sp(0.2),

    q(5, "Retrait du droit d'insertion"),
    code_block([
        "REVOKE INSERT ON HR.EMPLOYEES FROM EXAM_USER;",
    ]),
    note("Le REVOKE CASCADE révoque aussi le droit si EXAM_USER l'avait propagé avec GRANT OPTION."),
    sp(0.3),

    Paragraph("Partie B — Rôles et profils (3 pts)", S_PART),

    q(6, "Création du profil PROFIL_EXAM"),
    code_block([
        "CREATE PROFILE PROFIL_EXAM LIMIT",
        "  PASSWORD_LIFE_TIME    15",
        "  PASSWORD_GRACE_TIME   3",
        "  SESSIONS_PER_USER     2",
        "  CONNECT_TIME          30;",
    ]),
    sp(0.2),

    q(7, "Création de STAGIAIRE avec le profil"),
    code_block([
        "CREATE USER STAGIAIRE IDENTIFIED BY Stag2026",
        "  PROFILE PROFIL_EXAM",
        "  DEFAULT TABLESPACE USERS",
        "  TEMPORARY TABLESPACE TEMP;",
        "",
        "GRANT CONNECT TO STAGIAIRE;",
    ]),
    sp(0.2),

    q(8, "Création du rôle ROLE_LECTURE"),
    code_block([
        "CREATE ROLE ROLE_LECTURE;",
        "",
        "GRANT SELECT, INSERT ON HR.EMPLOYEES  TO ROLE_LECTURE;",
        "GRANT SELECT, INSERT ON HR.DEPARTMENTS TO ROLE_LECTURE;",
    ]),
    sp(0.2),

    q(9, "Attribution du rôle et vérification"),
    code_block([
        "GRANT ROLE_LECTURE TO STAGIAIRE;",
        "",
        "-- Vérification",
        "SELECT GRANTED_ROLE FROM DBA_ROLE_PRIVS WHERE GRANTEE = 'STAGIAIRE';",
        "SELECT TABLE_NAME, PRIVILEGE FROM DBA_TAB_PRIVS",
        "WHERE GRANTEE = 'ROLE_LECTURE';",
    ]),
    sp(0.2),

    q(10, "Suppression des objets et conséquences"),
    code_block([
        "-- 1. Supprimer le rôle",
        "DROP ROLE ROLE_LECTURE;",
        "-- Conséquence : le rôle est retiré de tous les utilisateurs qui le possèdent.",
        "",
        "-- 2. Supprimer le profil (CASCADE transfère les users vers DEFAULT)",
        "DROP PROFILE PROFIL_EXAM CASCADE;",
        "-- Conséquence : STAGIAIRE récupère le profil DEFAULT.",
        "",
        "-- 3. Supprimer l'utilisateur",
        "DROP USER STAGIAIRE CASCADE;",
        "-- Conséquence : tous les objets de STAGIAIRE sont supprimés.",
        "DROP USER EXAM_USER CASCADE;",
    ]),
    note("Barème : Q1=0.5 · Q2=0.5 · Q3=0.5 · Q4=0.5 · Q5=0.5 | Q6=0.5 · Q7=0.5 · Q8=0.5 · Q9=0.5 · Q10=0.5 pts"),
    sp(0.4),
]

# ══════════════════════════════════════════════════════════════════════════════
# BONUS — Procédure PL/SQL de synthèse  (+2 pts)
# ══════════════════════════════════════════════════════════════════════════════
story += [hr_blue(), ex_header("Bonus", "Procédure PL/SQL PS_GET_PRIV_ABOUT_USER", "+2 pts bonus"), sp(0.3)]

story += [
    code_block([
        "CREATE OR REPLACE PROCEDURE PS_GET_PRIV_ABOUT_USER(p_user IN VARCHAR2) IS",
        "BEGIN",
        "  DBMS_OUTPUT.PUT_LINE('====== Compte : ' || UPPER(p_user) || ' ======');",
        "",
        "  -- 1. Privilèges système",
        "  DBMS_OUTPUT.PUT_LINE('--- Privilèges Système ---');",
        "  FOR r IN (SELECT PRIVILEGE, ADMIN_OPTION",
        "            FROM DBA_SYS_PRIVS",
        "            WHERE GRANTEE = UPPER(p_user)) LOOP",
        "    DBMS_OUTPUT.PUT_LINE('  SYS: ' || r.PRIVILEGE ||",
        "        CASE WHEN r.ADMIN_OPTION = 'YES' THEN ' (ADMIN)' ELSE '' END);",
        "  END LOOP;",
        "",
        "  -- 2. Privilèges objet sur table",
        "  DBMS_OUTPUT.PUT_LINE('--- Privilèges Objet (table) ---');",
        "  FOR r IN (SELECT TABLE_NAME, PRIVILEGE, GRANTABLE",
        "            FROM DBA_TAB_PRIVS",
        "            WHERE GRANTEE = UPPER(p_user)) LOOP",
        "    DBMS_OUTPUT.PUT_LINE('  TAB: ' || r.TABLE_NAME || ' -> ' || r.PRIVILEGE);",
        "  END LOOP;",
        "",
        "  -- 3. Privilèges objet sur colonne",
        "  DBMS_OUTPUT.PUT_LINE('--- Privilèges Objet (colonne) ---');",
        "  FOR r IN (SELECT TABLE_NAME, COLUMN_NAME, PRIVILEGE",
        "            FROM DBA_COL_PRIVS",
        "            WHERE GRANTEE = UPPER(p_user)) LOOP",
        "    DBMS_OUTPUT.PUT_LINE('  COL: ' || r.TABLE_NAME || '.' || r.COLUMN_NAME ||",
        "         ' -> ' || r.PRIVILEGE);",
        "  END LOOP;",
        "",
        "  -- 4. Rôles",
        "  DBMS_OUTPUT.PUT_LINE('--- Rôles ---');",
        "  FOR r IN (SELECT GRANTED_ROLE, ADMIN_OPTION, DEFAULT_ROLE",
        "            FROM DBA_ROLE_PRIVS",
        "            WHERE GRANTEE = UPPER(p_user)) LOOP",
        "    DBMS_OUTPUT.PUT_LINE('  ROLE: ' || r.GRANTED_ROLE);",
        "  END LOOP;",
        "",
        "END PS_GET_PRIV_ABOUT_USER;",
        "/",
        "",
        "-- Test :",
        "SET SERVEROUTPUT ON;",
        "EXEC PS_GET_PRIV_ABOUT_USER('EXAM_USER');",
    ]),
    note("Bonus accordé si : structure correcte, curseurs FOR utilisés, affichage clair."),
    sp(0.5),
    hr_blue(2),
    sp(0.3),
    Paragraph("— Fin de la correction —", S_SUBTITLE),
    Paragraph("Bonne correction !", S_META),
]

# ── Build ──────────────────────────────────────────────────────────────────────
def on_page(canvas, doc):
    canvas.saveState()
    # header
    canvas.setStrokeColor(BLUE_DARK)
    canvas.setLineWidth(1.5)
    canvas.line(MARGIN, H - MARGIN + 0.3*cm, W - MARGIN, H - MARGIN + 0.3*cm)
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(colors.HexColor("#555555"))
    canvas.drawString(MARGIN, H - MARGIN + 0.5*cm,
                      "Correction — Examen TP Administration BDD Oracle — L2/L3 DSI 2025-2026")
    # footer
    canvas.line(MARGIN, MARGIN - 0.3*cm, W - MARGIN, MARGIN - 0.3*cm)
    canvas.drawCentredString(W/2, MARGIN - 0.6*cm,
                             f"Page {doc.page}")
    canvas.restoreState()

out = "/mnt/user-data/outputs/Correction_Examen_TP_DBA.pdf"
doc = SimpleDocTemplate(
    out, pagesize=A4,
    leftMargin=MARGIN, rightMargin=MARGIN,
    topMargin=MARGIN + 0.6*cm, bottomMargin=MARGIN + 0.4*cm,
)
doc.build(story, onFirstPage=on_page, onLaterPages=on_page)
print(f"PDF generated: {out}")
