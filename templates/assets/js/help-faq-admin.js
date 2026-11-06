(function () {
  'use strict';

  function uniqueIndex() {
    return 'n' + Date.now() + Math.floor(Math.random() * 10000);
  }

  function templateHtml(id) {
    var el = document.getElementById(id);
    return el ? el.innerHTML : '';
  }

  function buildCategoryRow() {
    var catIndex = uniqueIndex();
    return templateHtml('vc-help-faq-category-template').split('__CAT__').join(catIndex);
  }

  function buildQuestionRow(categoryEl) {
    var catIndex = categoryEl.getAttribute('data-vc-help-faq-cat-index');
    if (!catIndex) {
      catIndex = uniqueIndex();
      categoryEl.setAttribute('data-vc-help-faq-cat-index', catIndex);
    }
    var qIndex = uniqueIndex();
    return templateHtml('vc-help-faq-question-template')
      .split('__CAT__').join(catIndex)
      .split('__Q__').join(qIndex);
  }

  function onAddCategory() {
    var container = document.getElementById('vc-help-faq-categories');
    if (!container) return;
    container.insertAdjacentHTML('beforeend', buildCategoryRow());
  }

  function onAddQuestion(button) {
    var categoryEl = button.closest('[data-vc-help-faq-category]');
    var questionsEl = categoryEl ? categoryEl.querySelector('[data-vc-help-faq-questions]') : null;
    if (!categoryEl || !questionsEl) return;
    questionsEl.insertAdjacentHTML('beforeend', buildQuestionRow(categoryEl));
  }

  document.addEventListener('DOMContentLoaded', function () {
    var addCategoryButton = document.getElementById('vc-help-faq-add-category');
    if (addCategoryButton) {
      addCategoryButton.addEventListener('click', onAddCategory);
    }

    document.addEventListener('click', function (event) {
      var addQuestionButton = event.target.closest('[data-vc-help-faq-add-question]');
      if (addQuestionButton) {
        onAddQuestion(addQuestionButton);
        return;
      }

      var removeCategoryButton = event.target.closest('[data-vc-help-faq-remove-category]');
      if (removeCategoryButton) {
        var categoryEl = removeCategoryButton.closest('[data-vc-help-faq-category]');
        if (categoryEl) categoryEl.remove();
        return;
      }

      var removeQuestionButton = event.target.closest('[data-vc-help-faq-remove-question]');
      if (removeQuestionButton) {
        var rowEl = removeQuestionButton.closest('[data-vc-help-faq-question-row]');
        if (rowEl) rowEl.remove();
      }
    });
  });
})();
