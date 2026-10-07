import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

export const TYPES = ['feat', 'fix', 'docs', 'chore', 'refactor', 'test', 'perf'];
export const CLASSES = ['producto', 'cliente', 'interno'];
const TITLE = new RegExp(`^(${TYPES.join('|')})(\\([\\w./-]+\\))?: \\S`);

export function checkTitle(title) {
  return TITLE.test(String(title ?? '').trim())
    ? []
    : [`El título debe empezar con un tipo (${TYPES.join(', ')}) seguido de ": ". Ejemplo: "feat: agenda por doctor".`];
}

function sectionLines(body) {
  const out = [];
  let found = false;
  let comment = false;
  let fence = null;
  for (const line of String(body ?? '').split(/\r?\n/)) {
    if (comment) {
      if (line.includes('-->')) comment = false;
      continue;
    }
    const marker = line.match(/^\s*(```|~~~)/);
    if (marker) {
      if (fence === null) fence = marker[1];
      else if (fence === marker[1]) fence = null;
      continue;
    }
    if (fence !== null) continue;
    if (/^\s*<!--/.test(line)) {
      if (!line.includes('-->')) comment = true;
      continue;
    }
    if (/^##[ \t]/.test(line)) {
      if (found) break;
      if (/^##[ \t]+Novedad[ \t]*$/i.test(line)) found = true;
      continue;
    }
    if (found) out.push(line);
  }
  return found ? out : null;
}

export function parseNovedad(body) {
  const lines = sectionLines(body);
  if (lines === null) return null;
  const result = {};
  for (const line of lines) {
    const match = line.match(/^[ \t]*[-*][ \t]*(pública|publica|interna|hito)[ \t]*:(.*)$/i);
    if (!match) continue;
    const key = match[1].toLowerCase() === 'publica' ? 'pública' : match[1].toLowerCase();
    result[key] = match[2].replace(/\s+/g, ' ').trim();
  }
  return result;
}

export function checkNovedad(body, clase) {
  if (!CLASSES.includes(clase)) return [`Clase de repo desconocida en .github/marbust.json: "${clase}".`];
  const novedad = parseNovedad(body);
  if (novedad === null) return ['Falta la sección "## Novedad" de la plantilla.'];
  const errors = [];
  if (!novedad.interna) errors.push('La línea "interna" de la Novedad está vacía o falta. Escribe "ninguna" si no aplica.');
  if (clase === 'producto') {
    if (!novedad['pública']) errors.push('La línea "pública" de la Novedad está vacía o falta. Escribe "ninguna" si no aplica.');
    if (!/^(sí|si|no)$/i.test(novedad.hito ?? '')) errors.push('La línea "hito" debe ser "sí" o "no".');
  } else {
    if ('pública' in novedad || 'hito' in novedad) {
      errors.push(`Un repo de clase "${clase}" no publica novedades: quita las líneas "pública" e "hito".`);
    }
  }
  return errors;
}

export const DEPENDENCY_BOTS = ['dependabot[bot]', 'renovate[bot]'];

export function isDependencyBot(pr) {
  return pr?.user?.type === 'Bot' && DEPENDENCY_BOTS.includes(pr?.user?.login);
}

export function runChecks(pr, clase) {
  if (isDependencyBot(pr)) return [];
  return [...checkTitle(pr?.title), ...checkNovedad(pr?.body, clase)];
}

function main() {
  const event = JSON.parse(readFileSync(process.env.GITHUB_EVENT_PATH, 'utf8'));
  const config = JSON.parse(readFileSync(new URL('../marbust.json', import.meta.url), 'utf8'));
  const errors = runChecks(event.pull_request, config.clase);
  if (errors.length) {
    for (const e of errors) console.error(`✗ ${e}`);
    process.exit(1);
  }
  console.log('✓ Título y sección Novedad correctos.');
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
