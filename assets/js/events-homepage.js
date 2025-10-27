jQuery(document).ready(function ($) {
  if (typeof elmEventsData === "undefined") return;

  const allEvents = Array.isArray(elmEventsData.events) ? elmEventsData.events : [];
  const $eventsList = $("#events_homepage_list");

  if (!$eventsList.length) return;

  const upcoming = allEvents
    .filter((ev) => new Date(ev.date) >= new Date())
    .sort((a, b) => new Date(a.date) - new Date(b.date))
    .slice(0, 3); // show 3 events max

  if (upcoming.length === 0) {
    $eventsList.html("<p>Brak nadchodzących wydarzeń.</p>");
    return;
  }

  upcoming.forEach((ev) => {
    const dateObj = new Date(ev.date + "T00:00:00");
    const day = String(dateObj.getDate()).padStart(2, "0");
    const month = String(dateObj.getMonth() + 1).padStart(2, "0");

    const html = `
      <div class="events-box__element">
        <div class="events-box__element-title">
          <svg width="5" height="11" viewBox="0 0 5 11" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 5.5C5 5.5 5 6.10156 5 11C2.23858 11 0 8.53757 0 5.5C0 2.46243 2.23858 0 5 0C5 3.69531 5 5.5 5 5.5Z" fill="#D1232A"></path>
          </svg>
          ${day}.${month} / <a href="${ev.link}">${ev.title}</a>
        </div>
        ${ev.excerpt ? `<p class="events-box__element-excerpt">${ev.excerpt}</p>` : ""}
      </div>
    `;

    $eventsList.append(html);
  });
});
