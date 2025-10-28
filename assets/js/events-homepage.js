jQuery(document).ready(function ($) {
  if (typeof elmEventsData === "undefined") return;

  const allEvents = Array.isArray(elmEventsData.events) ? elmEventsData.events : [];
  const $eventsList = $("#elm_events_homepage_list");
  $(".elm_events-box__more").attr("href", elmEventsData.archiveUrl || "#");

  if (!$eventsList.length) return;

  const today = new Date();
  const upcoming = allEvents
    .filter((ev) => new Date(ev.date) >= today)
    .sort((a, b) => new Date(a.date) - new Date(b.date))
    .slice(0, 3); // limit to 3

  if (upcoming.length === 0) {
    $eventsList.html("<p>Brak nadchodzących wydarzeń.</p>");
    return;
  }

  upcoming.forEach((ev) => {
    const dateObj = new Date(ev.date + "T00:00:00");
    const day = String(dateObj.getDate()).padStart(2, "0");
    const month = String(dateObj.getMonth() + 1).padStart(2, "0");

    const html = `
      <div class="elm_events-box__element">
        <div class="elm_events-box__element-title">
        
          ${day}.${month} / ${ev.title.length > 60 ? ev.title.substring(0, 60) + "..." : ev.title}
        </div> 
        ${
          ev.excerpt
            ? `<p class="elm_events-box__element-excerpt">
          ${ev.excerpt.length > 100 ? ev.excerpt.substring(0, 100) + "..." : ev.excerpt}
          </p>`
            : ""
        }
      </div>
    `;
    $eventsList.append(html);
  });
});
