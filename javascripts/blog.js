$(() => {
  if (!$("#blog").length) return;

  let on;
  $(".posts a").hover(
    function () {
      on = RoughNotation.annotate($("strong", this)[0], {
        type: "highlight",
        color: "#f8e9b1",
      });
      on.show();
    },
    function () {
      if (on) {
        on.remove();
      }
    }
  );

  $(document).on("scroll", () => {
    $("body").toggleClass("page-scroll", $(window).scrollTop() !== 0);
  });
});
