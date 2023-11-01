<button id="wordai-btn">WordAi</button>
<div id="wordai-modal" title="WordAi Settings" style="display:none;">
    <p>Select the processing options:</p>
    <select id="wordai-mode">
        <option value="rewrite">Rewrite</option>
        <option value="avoid">Avoid AI Detection</option>
    </select>
    <select id="wordai-uniqueness">
        <option value="1">Conservative</option>
        <option value="2">Regular</option>
        <option value="3">Adventurous</option>
    </select>
    <textarea id="wordai-synonyms" placeholder="Custom Synonyms (comma separated)"></textarea>
    <textarea id="wordai-protected" placeholder="Protected Words (comma separated)"></textarea>
    <button id="wordai-process">Process with WordAi</button>
</div>
