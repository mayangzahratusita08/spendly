/*
 * Spendly Material 3 Expressive application integration.
 * Third-party M3E Web 1.8.0; PHP forms and server validation remain authoritative.
 */
import { createTheme } from '@kyralabs/m3exp-web/theme';
import { materialShape, polygonToPath, toSvgPathData } from '@kyralabs/m3e-shapes';
import '@kyralabs/m3e-elements/button-group.js';
import '@kyralabs/m3e-elements/chip-set.js';
import '@kyralabs/m3e-elements/fab-menu.js';
import '@kyralabs/m3e-elements/linear-progress.js';
import '@kyralabs/m3e-elements/ripple.js';

const root=document.documentElement;
const reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)');

try {
 const theme=createTheme({seed:'#416B50',variant:'expressive',mode:'light',motionScheme:'expressive',specVersion:'2025'});
 const style=document.createElement('style');
 style.id='spendly-m3e-generated-theme';
 style.textContent=theme.cssText;
 document.head.append(style);
} catch(error) {
 console.error('[Spendly M3E] Theme generation failed; component defaults remain active.',error);
}

function renderMaterialShapes() {
 document.querySelectorAll('svg[data-material-shape]').forEach(svg=>{
  const name=svg.dataset.materialShape, path=svg.querySelector('path');
  if(!name||!path) return;
  try {
   path.setAttribute('d',toSvgPathData(polygonToPath(materialShape(name))));
   path.setAttribute('fill','currentColor');
   svg.setAttribute('aria-hidden','true');
  } catch(error) { console.warn('[Spendly M3E] Shape preset unavailable:',name,error); }
 });
}

function connectTransactionTypeGroup() {
 const group=document.getElementById('transaction-type-group');
 const form=document.getElementById('transaction-form');
 if(!group||!form) return;
 group.addEventListener('selection-change',event=>{
  const value=String(event.detail?.value??event.detail?.values?.[0]??'');
  if(!['income','expense'].includes(value)) return;
  const radio=form.querySelector('input[name="type"][value="'+value+'"]');
  if(!radio) return;
  radio.checked=true;
  radio.dispatchEvent(new Event('change',{bubbles:true}));
 });
}

function connectTransactionFilterChips() {
 const form=document.getElementById('transaction-filter-form');
 const select=document.getElementById('filter-type');
 const group=document.querySelector('.m3e-filter-chip-group');
 if(!form||!select||!group) return;
 const inputs=Array.from(group.querySelectorAll('[data-m3e-filter-type]'));
 if(!inputs.length) return;
 group.hidden=false;
 const fallback=select.closest('.m3e-legacy-type-filter');
 if(fallback) fallback.hidden=true;
 inputs.forEach(input=>{
  input.addEventListener('change',()=>{
   if(input.checked) inputs.forEach(other=>{if(other!==input) other.checked=false;});
   else if(!inputs.some(item=>item.checked)) {
    const all=inputs.find(item=>item.dataset.m3eFilterType==='all');
    if(all) all.checked=true;
   }
   const selected=inputs.find(item=>item.checked);
   const value=selected?.dataset.m3eFilterType??'all';
   select.value=value==='all'?'':value;
   if(typeof form.requestSubmit==='function') form.requestSubmit();
   else form.submit();
  });
 });
}

function connectContextualActions() {
 document.querySelectorAll('[data-m3e-context-actions]').forEach(menu=>{
  menu.addEventListener('action',event=>{
   const action=String(event.detail?.value??'');
   const destinations={expense:menu.dataset.expenseUrl,income:menu.dataset.incomeUrl,budget:menu.dataset.budgetUrl};
   const target=destinations[action];
   if(!target) return;
   try {
    const url=new URL(target,window.location.href);
    if(url.origin===window.location.origin) window.location.assign(url.href);
   } catch(error) { console.error('[Spendly M3E] Invalid contextual action URL.',error); }
  });
 });
}

function initialize() {
 renderMaterialShapes();
 connectTransactionTypeGroup();
 connectTransactionFilterChips();
 connectContextualActions();
 root.classList.add('m3e-app-ready');
 if(reducedMotion.matches) root.classList.add('m3e-reduced-motion');
}
if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',initialize,{once:true});
else initialize();
reducedMotion.addEventListener?.('change',()=>root.classList.toggle('m3e-reduced-motion',reducedMotion.matches));
