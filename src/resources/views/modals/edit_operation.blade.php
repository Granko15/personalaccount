<div id="edit-operation-modal" class="modal-box">

  <div class="modal">
    <span class="close-modal"><i class="bi bi-x"></i></span>
    <h2>Upraviť operáciu</h2>
    <div>
      <input class="operation_type" name="operation_type" type="radio" value="income"><label>Príjem</label>
      <input class="operation_type" name="operation_type" type="radio" value="expense"><label>Výdavok</label>

    </div>
    <select id="operation_choice" name="typ">
      <option value="default_opt">Vyberte typ operácie</option>

      <option class="expense_opt" value="">Náklady na služobnú cestu</option>
      <option class="expense_opt" value="">Malý nákup</option>
      <option class="expense_opt" value="">Nákup na faktúru</option>
      <option class="expense_opt" value="">Nákup z Marquetu</option>
      <option class="expense_opt" value="">Pôžička pre niekoho</option>

      <option class="income_opt" value="">Zo služby s faktúrou</option>
      <option class="income_opt" value="">Projektový grant</option>
      <option class="income_opt" value="">Pôžička od niekoho</option>
      <option class="income_opt" value="">Splatenie pôžičky od niekoho</option>

    </select>
    <input type="text" placeholder="Názov">
    <input type="text" placeholder="Subjekt">
    <input type="text" placeholder="Suma">
    <input type="text" placeholder="Názov">
    <label>Splatné do:</label><input type="date" placeholder="dd.mm.yyyy">
    <input type="file" id="operation_file" name="" accept=".doc, .docx, .pdf">

    <button type="button" class="create">Uložiť</button>

  </div>

</div>