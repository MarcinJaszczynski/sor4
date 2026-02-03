// FullCalendar page initializer (lazy)

function qs(selector) {
    return document.querySelector(selector);
}

async function initCalendarPage() {
    const root = qs('[data-bprafa-calendar]');
    if (!root) return;

    const feedUrl = root.getAttribute('data-feed-url');
    const createNoteUrl = root.getAttribute('data-create-note-url');
    const createEventUrl = root.getAttribute('data-create-event-url');

    // FullCalendar v5 requires VDOM bootstrap before loading plugins.
    // This import defines globalThis.FullCalendarVDom which @fullcalendar/common expects.
    await import('@fullcalendar/core/vdom.js');

    // Now load core + locale + plugins.
    const { Calendar } = await import('@fullcalendar/core');
    await import('@fullcalendar/core/locales/pl');

    const dayGridPlugin = (await import('@fullcalendar/daygrid')).default;
    const timeGridPlugin = (await import('@fullcalendar/timegrid')).default;
    const interactionPlugin = (await import('@fullcalendar/interaction')).default;
    const listPlugin = (await import('@fullcalendar/list')).default;

    await import('@fullcalendar/common/main.css');
    await import('@fullcalendar/daygrid/main.css');
    await import('@fullcalendar/timegrid/main.css');
    await import('@fullcalendar/list/main.css');

    const calendar = new Calendar(root, {
        plugins: [
            dayGridPlugin,
            timeGridPlugin,
            interactionPlugin,
            listPlugin,
        ],
        initialView: 'dayGridMonth',
        height: 'auto',
        nowIndicator: true,
        selectable: true,
        editable: false,
        dayMaxEvents: true,
        firstDay: 1, // Monday
        locale: 'pl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: {
            today: 'Dziś',
            month: 'Miesiąc',
            week: '7 dni',
            day: 'Dzień',
            list: 'Lista',
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        },
        events: async (info, successCallback, failureCallback) => {
            try {
                const url = new URL(feedUrl, window.location.origin);
                url.searchParams.set('start', info.startStr);
                url.searchParams.set('end', info.endStr);

                const res = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!res.ok) {
                    throw new Error(`Feed error: ${res.status}`);
                }

                const data = await res.json();
                successCallback(data);
            } catch (e) {
                console.error(e);
                failureCallback(e);
            }
        },
        eventClick: (info) => {
            const url = info.event.url;
            if (url) {
                info.jsEvent.preventDefault();
                window.location.href = url;
            }
        },
        select: (info) => {
            // Range select: create an event (best UX for multi-day)
            if (createEventUrl) {
                const url = new URL(createEventUrl, window.location.origin);
                url.searchParams.set('start_date', info.startStr);
                // FullCalendar provides endStr as exclusive for all-day selections; pass inclusive end_date
                const end = info.end ? new Date(info.end.getTime() - 86400000) : null;
                if (end) {
                    url.searchParams.set('end_date', end.toISOString().slice(0, 10));
                }
                window.open(url.toString(), '_blank');
            }
        },
        dateClick: (info) => {
            // Single day click: create a note
            if (createNoteUrl) {
                const url = new URL(createNoteUrl, window.location.origin);
                url.searchParams.set('date', info.dateStr);
                window.open(url.toString(), '_blank');
            }
        },
        customButtons: {
            createEvent: {
                text: 'Nowa impreza',
                click: () => {
                    if (!createEventUrl) return;
                    window.open(createEventUrl, '_blank');
                },
            },
            createNote: {
                text: 'Nowa notatka',
                click: () => {
                    if (!createNoteUrl) return;
                    window.open(createNoteUrl, '_blank');
                },
            },
        },
    });

    // Add custom buttons to left side
    calendar.setOption('headerToolbar', {
        left: 'prev,next today createEvent,createNote',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
    });

    calendar.render();
}

document.addEventListener('DOMContentLoaded', () => {
    initCalendarPage();
});
