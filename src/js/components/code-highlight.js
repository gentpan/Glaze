import hljs from 'highlight.js/lib/core';
import bash from 'highlight.js/lib/languages/bash';
import css from 'highlight.js/lib/languages/css';
import diff from 'highlight.js/lib/languages/diff';
import dockerfile from 'highlight.js/lib/languages/dockerfile';
import go from 'highlight.js/lib/languages/go';
import ini from 'highlight.js/lib/languages/ini';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import markdown from 'highlight.js/lib/languages/markdown';
import nginx from 'highlight.js/lib/languages/nginx';
import php from 'highlight.js/lib/languages/php';
import python from 'highlight.js/lib/languages/python';
import sql from 'highlight.js/lib/languages/sql';
import typescript from 'highlight.js/lib/languages/typescript';
import xml from 'highlight.js/lib/languages/xml';
import yaml from 'highlight.js/lib/languages/yaml';
import { showToast } from './toast.js';

const languages = { bash, css, diff, dockerfile, go, ini, javascript, json, markdown, nginx, php, python, sql, typescript, xml, yaml };
for (const [name, grammar] of Object.entries(languages)) hljs.registerLanguage(name, grammar);

function requestedLanguage(block, code) {
  const languageClass = [...code.classList, ...block.classList].find(name => /^(?:language|lang)-/.test(name));
  return (code.dataset.language || block.dataset.language || languageClass?.replace(/^(?:language|lang)-/, '') || '').trim().toLowerCase();
}

function highlight(block, code, source, language) {
  if (block.matches('.hljs, .nohighlight, .no-highlight') || code.matches('.hljs, .nohighlight, .no-highlight') || code.children.length || source.length > 50000) return '';
  if (language && !hljs.getLanguage(language)) return '';
  const text = source.trim();
  if (!language && /^(?:https?:\/\/\S+(?:\s+|$))+$/i.test(text)) return '';

  const syntax = language || (/^<[a-z!/]/i.test(text) ? 'html' : '');
  const result = syntax ? hljs.highlight(source, { language: syntax }) : hljs.highlightAuto(source);
  if (result.language) {
    code.innerHTML = result.value;
    code.classList.add('hljs');
  }
  return result.language || '';
}

const labels = window.glazeCodeLabels ?? {
  copy: 'Copy code',
  copied: 'Code copied',
  error: 'Could not copy the code.',
  collapse: 'Collapse code',
  expand: 'Expand code',
};
const shortNames = { xml: 'HTML / XML', html: 'HTML', css: 'CSS', json: 'JSON', sql: 'SQL', php: 'PHP', yaml: 'YAML', ini: 'INI' };
const previewLines = 20;

function languageLabel(language) {
  if (!language || /^(?:plain|plaintext|text|txt|none|nohighlight|no-highlight)$/.test(language)) return '';
  return Object.hasOwn(shortNames, language) ? shortNames[language] : hljs.getLanguage(language)?.name || language;
}

function codeText(code) {
  if (!code.querySelector('br')) return code.textContent;
  const clone = code.cloneNode(true);
  for (const lineBreak of clone.querySelectorAll('br')) lineBreak.replaceWith('\n');
  return clone.textContent;
}

async function copyCode(source, button) {
  try {
    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(source);
      return true;
    }
  } catch {
    // Some browsers provide the API without granting permission to the page.
  }

  const field = document.createElement('textarea');
  field.value = source;
  field.readOnly = true;
  field.className = 'glaze-code-clipboard';
  document.body.append(field);
  try {
    field.focus({ preventScroll: true });
    field.select();
    return document.execCommand('copy');
  } catch {
    return false;
  } finally {
    field.remove();
    button.focus({ preventScroll: true });
  }
}

function action(label, className) {
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'glaze-code-action ' + className;
  button.textContent = label;
  return button;
}

function firstLines(block, count) {
  const range = document.createRange();
  range.selectNodeContents(block);
  const walker = document.createTreeWalker(block, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT);

  for (let node = walker.nextNode(); node; node = walker.nextNode()) {
    if (node.nodeName === 'BR' && --count === 0) {
      range.setEndBefore(node);
      break;
    }
    if (node.nodeType !== Node.TEXT_NODE) continue;
    for (const newline of node.data.matchAll(/\r\n?|\n/g)) {
      if (--count !== 0) continue;
      range.setEnd(node, newline.index);
      return range.cloneContents();
    }
  }
  return range.cloneContents();
}

function addFold(panel, block) {
  const fold = action(labels.expand, 'glaze-code-fold');
  if (!block.id) {
    let id;
    do id = 'glaze-code-' + ++blockNumber;
    while (document.getElementById(id));
    block.id = id;
  }
  fold.setAttribute('aria-controls', block.id);
  fold.setAttribute('aria-expanded', 'false');

  const original = [...block.childNodes];
  let expanded = false;
  block.replaceChildren(firstLines(block, previewLines));
  fold.addEventListener('click', () => {
    expanded = !expanded;
    if (expanded) {
      block.replaceChildren(...original);
    } else {
      block.replaceChildren(firstLines(block, previewLines));
    }
    fold.setAttribute('aria-expanded', String(expanded));
    fold.textContent = expanded ? labels.collapse : labels.expand;
    if (!expanded) fold.scrollIntoView({ block: 'nearest' });
  });

  const footer = document.createElement('div');
  footer.className = 'glaze-code-footer';
  footer.append(fold);
  panel.append(footer);
}

function addToolbar(block, source, language) {
  const lineCount = source.replace(/(?:\r\n?|\n)$/, '').split(/\r\n?|\n/).length;
  const panel = document.createElement('div');
  panel.className = 'glaze-code-block';
  const copy = action('', 'glaze-code-copy');
  copy.setAttribute('aria-label', labels.copy);
  copy.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <g class="glaze-code-copy-icon"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 4H6a2 2 0 0 0-2 2v10"/></g>
    <path class="glaze-code-copy-check" d="m5 12 4 4L19 6"/>
  </svg>`;

  let reset;
  copy.addEventListener('click', async () => {
    if (copy.getAttribute('aria-busy') === 'true') return;
    clearTimeout(reset);
    delete copy.dataset.copyState;
    copy.setAttribute('aria-busy', 'true');
    const copied = await copyCode(source, copy);
    copy.removeAttribute('aria-busy');
    if (copied) {
      copy.dataset.copyState = 'copied';
      reset = setTimeout(() => delete copy.dataset.copyState, 2200);
    }
    showToast(copied ? labels.copied : labels.error, copied ? 'success' : 'error', copy);
  });

  block.before(panel);
  if (lineCount === 1) {
    const line = document.createElement('div');
    line.className = 'glaze-code-line';
    line.append(block, copy);
    panel.append(line);
  } else {
    const toolbar = document.createElement('div');
    toolbar.className = 'glaze-code-toolbar';
    const label = languageLabel(language);
    if (label) {
      const badge = document.createElement('span');
      badge.className = 'glaze-code-language';
      badge.textContent = label;
      toolbar.append(badge);
    }
    toolbar.append(copy);
    panel.append(toolbar, block);
  }
  if (lineCount > previewLines) addFold(panel, block);
}

let blockNumber = 0;
for (const block of document.querySelectorAll('.entry-content pre:not([hidden])')) {
  if (block.closest('.glaze-code-block')) continue;
  const code = block.querySelector(':scope > code') ?? block;
  const source = codeText(code);
  if (!source.trim()) continue;
  const language = requestedLanguage(block, code);
  const detected = highlight(block, code, source, language);
  addToolbar(block, source, language || detected);
}
