<?hh // strict

class :fbbranding extends :x:element {
  category %flow;

  protected string $tagName = 'fbbranding';

  protected function render(): XHPRoot {
    return
      <span class="branding-el">
        <!--
        <svg class="icon icon--social-facebook">
          <use href="#icon--social-facebook" />
        </svg>
        -->
        <span class="has-icon"> Built for Angelhack HK 2016</span>
      </span>;
  }
}
