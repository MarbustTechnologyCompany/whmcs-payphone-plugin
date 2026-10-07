import { test } from 'node:test';
import assert from 'node:assert/strict';
import { checkTitle, parseNovedad, checkNovedad, runChecks } from './pr-checks.mjs';

const body = (lines) => `## Resumen\n\nAlgo.\n\n## Novedad\n\n<!-- comentario\nde varias líneas -->\n\n${lines}\n\n## Issue vinculado\n\nCloses #1\n`;
const completa = body('- pública: MediMarbust ya envía recordatorios por correo.\n- interna: cron de recordatorios con tope por licencia.\n- hito: no');

test('el título necesita un tipo válido', () => {
  assert.deepEqual(checkTitle('feat: agenda por doctor'), []);
  assert.deepEqual(checkTitle('fix(api): licencia vencida'), []);
  assert.equal(checkTitle('Agenda por doctor').length, 1);
  assert.equal(checkTitle('feature: agenda').length, 1);
  assert.equal(checkTitle('feat:sin espacio').length, 1);
  assert.equal(checkTitle('').length, 1);
});

test('lee la sección Novedad e ignora comentarios, bloques de código y otras secciones', () => {
  const b = body('```\n- pública: dentro de un bloque\n```\n- pública: real\n- interna: técnica\n- hito: sí') + '\n- interna: de otra sección';
  assert.deepEqual(parseNovedad(b), { 'pública': 'real', interna: 'técnica', hito: 'sí' });
});

test('acepta "publica" sin tilde', () => {
  assert.equal(parseNovedad(body('- publica: algo'))['pública'], 'algo');
});

test('sin sección Novedad devuelve null y el check falla', () => {
  assert.equal(parseNovedad('## Resumen\n\nNada'), null);
  assert.equal(checkNovedad('## Resumen\n\nNada', 'producto').length, 1);
});

test('producto: la Novedad completa pasa', () => {
  assert.deepEqual(checkNovedad(completa, 'producto'), []);
});

test('producto: "ninguna" es una respuesta válida', () => {
  assert.deepEqual(checkNovedad(body('- pública: ninguna\n- interna: ninguna\n- hito: no'), 'producto'), []);
});

test('producto: líneas vacías o hito inválido fallan', () => {
  assert.equal(checkNovedad(body('- pública:\n- interna: algo\n- hito: no'), 'producto').length, 1);
  assert.equal(checkNovedad(body('- pública: algo\n- interna:\n- hito: no'), 'producto').length, 1);
  assert.equal(checkNovedad(body('- pública: algo\n- interna: algo\n- hito: tal vez'), 'producto').length, 1);
  assert.equal(checkNovedad(body('- pública: algo\n- interna: algo'), 'producto').length, 1);
});

test('cliente e interno: solo la línea interna; la pública se rechaza', () => {
  assert.deepEqual(checkNovedad(body('- interna: arreglo del formulario'), 'cliente'), []);
  assert.deepEqual(checkNovedad(body('- interna: ninguna'), 'interno'), []);
  assert.equal(checkNovedad(completa, 'cliente').length, 1);
  assert.equal(checkNovedad(body('- interna:'), 'interno').length, 1);
});

test('clase desconocida falla', () => {
  assert.equal(checkNovedad(completa, 'otra').length, 1);
});

test('solo dependabot y renovate se saltan los checks, con su login exacto', () => {
  assert.deepEqual(runChecks({ title: 'Bump x', body: '', user: { login: 'dependabot[bot]', type: 'Bot' } }, 'producto'), []);
  assert.deepEqual(runChecks({ title: 'Bump x', body: '', user: { login: 'renovate[bot]', type: 'Bot' } }, 'producto'), []);
  assert.equal(runChecks({ title: 'Bump x', body: '', user: { login: 'MarAntBQ', type: 'User' } }, 'producto').length, 2);
  assert.equal(runChecks({ title: 'Bump x', body: '', user: { login: 'my-dependabot-fan', type: 'User' } }, 'producto').length, 2);
  assert.equal(runChecks({ title: 'Bump x', body: '', user: { login: 'otro-bot[bot]', type: 'Bot' } }, 'producto').length, 2);
  assert.equal(runChecks({ title: 'Bump x', body: '', user: { login: 'dependabot[bot]', type: 'User' } }, 'producto').length, 2);
});

test('el encabezado acepta mayúsculas y espacios al final', () => {
  const b = (h) => `${h}\n\n- pública: algo\n- interna: algo\n- hito: no\n`;
  assert.deepEqual(checkNovedad(b('## NOVEDAD'), 'producto'), []);
  assert.deepEqual(checkNovedad(b('## novedad   '), 'producto'), []);
  assert.equal(parseNovedad(b('### Novedad')), null);
});

test('funciona con saltos de línea CRLF', () => {
  const crlf = completa.replace(/\n/g, '\r\n');
  assert.deepEqual(checkNovedad(crlf, 'producto'), []);
  assert.deepEqual(parseNovedad(crlf), { 'pública': 'MediMarbust ya envía recordatorios por correo.', interna: 'cron de recordatorios con tope por licencia.', hito: 'no' });
});

test('una línea con solo espacios cuenta como vacía', () => {
  assert.equal(checkNovedad(body('- pública:    \t\n- interna: algo\n- hito: no'), 'producto').length, 1);
});

test('un bloque abierto con ``` no se cierra con ~~~', () => {
  const b = body('```\n~~~\n- pública: falsa\n- interna: falsa\n- hito: no\n```\n- interna: real');
  assert.deepEqual(parseNovedad(b), { interna: 'real' });
  assert.equal(checkNovedad(b, 'producto').length, 2);
});
