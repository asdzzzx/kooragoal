(function ($) {
    'use strict';

    const settings = window.kooragoalSettings || {};
    const restBase = settings.restUrl ? settings.restUrl.replace(/\/$/, '') : '';
    const defaultLeagues = settings.defaultLeagueIds || [];
    const refreshInterval = settings.refreshInterval || 15000;

    function formatDate(date) {
        const year = date.getUTCFullYear();
        const month = String(date.getUTCMonth() + 1).padStart(2, '0');
        const day = String(date.getUTCDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function resolveEventIcon(type, detail) {
        const icons = {
            Goal: 'icon-goal.svg',
            CardYellow: 'icon-yellow-card.svg',
            CardRed: 'icon-red-card.svg',
            Substitution: 'icon-substitution.svg'
        };
        let key = 'Goal';
        if (type === 'Card') {
            key = detail === 'Yellow Card' ? 'CardYellow' : 'CardRed';
        } else if (type === 'Substitution') {
            key = 'Substitution';
        }
        const filename = icons[key] || icons.Goal;
        if (!settings.themeUrl) {
            return '';
        }
        return `<img src="${settings.themeUrl}/assets/images/${filename}" alt="" class="event-icon" loading="lazy">`;
    }

    function createFixtureCard(fixture) {
        const league = fixture.league || {};
        const teams = fixture.teams || { home: {}, away: {} };
        const score = fixture.score || { home: 0, away: 0 };
        const leagueLogo = league.logo ? `<img src="${league.logo}" alt="${league.name || ''}" class="fixture-card__league-logo" loading="lazy">` : '';
        const homeLogo = teams.home?.logo ? `<img src="${teams.home.logo}" alt="${teams.home.name || ''}" class="fixture-card__team-logo" loading="lazy">` : '';
        const awayLogo = teams.away?.logo ? `<img src="${teams.away.logo}" alt="${teams.away.name || ''}" class="fixture-card__team-logo" loading="lazy">` : '';
        const cardBg = `${settings.themeUrl || ''}/assets/images/match-card-bg.png`;
        let detailsLink = '#';
        let disabledClass = ' fixture-card__details--disabled';
        let disabledAttr = ' aria-disabled="true"';
        if (settings.matchPage) {
            detailsLink = `${settings.matchPage}?fixture=${fixture.id}`;
            disabledClass = '';
            disabledAttr = '';
        }

        return `
        <article class="fixture-card" data-fixture-id="${fixture.id}">
            <header class="fixture-card__header">
                ${leagueLogo}
                <div class="fixture-card__meta">
                    <span class="fixture-card__league-name">${league.name || ''}</span>
                    <span class="fixture-card__status badge">${fixture.statusLabel || ''}</span>
                </div>
                <time datetime="${fixture.date}T${fixture.time}" class="fixture-card__time">${fixture.time || ''}</time>
            </header>
            <div class="fixture-card__body" style="background-image: url('${cardBg}');">
                <div class="fixture-card__team fixture-card__team--home">
                    ${homeLogo}
                    <span class="fixture-card__team-name">${teams.home?.name || ''}</span>
                </div>
                <div class="fixture-card__score">
                    <span class="fixture-card__score-value">${score.home ?? 0}</span>
                    <span class="fixture-card__score-separator">-</span>
                    <span class="fixture-card__score-value">${score.away ?? 0}</span>
                </div>
                <div class="fixture-card__team fixture-card__team--away">
                    ${awayLogo}
                    <span class="fixture-card__team-name">${teams.away?.name || ''}</span>
                </div>
            </div>
            <footer class="fixture-card__footer">
                <a class="fixture-card__details${disabledClass}" href="${detailsLink}"${disabledAttr}>${settings.translations?.details || 'عرض تفاصيل المباراة'}</a>
            </footer>
        </article>`;
    }

    function renderFixtures(container, fixtures) {
        if (!container) {
            return;
        }
        if (!fixtures.length) {
            container.innerHTML = `<p class="fixtures-list__empty">${settings.translations?.noMatches || ''}</p>`;
            return;
        }
        const cards = fixtures.map(createFixtureCard).join('');
        container.innerHTML = `<div class="fixtures-grid">${cards}</div>`;
    }

    function fetchJSON(url) {
        return fetch(url, {
            headers: {
                'X-WP-Nonce': settings.nonce || ''
            },
            credentials: 'same-origin'
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
    }

    function fetchFixtures(state, target) {
        const leagues = state.leagues.length ? state.leagues.join(',') : '';
        const endpoint = `${restBase}/fixtures?date=${encodeURIComponent(state.date)}${leagues ? `&leagues=${leagues}` : ''}`;
        return fetchJSON(endpoint).then(data => {
            const fixtures = (data.fixtures || []).map(item => ({
                id: item.id,
                league: item.league,
                teams: item.teams,
                score: item.score,
                date: item.date,
                time: item.time,
                statusLabel: item.status_label || (item.status && item.status.long) || ''
            }));
            renderFixtures(target, fixtures);
        }).catch(() => {
            if (target) {
                target.innerHTML = `<p class="fixtures-list__empty">${settings.translations?.loading || ''}</p>`;
            }
        });
    }

    function renderStandings(container, rows) {
        if (!container) {
            return;
        }
        if (!rows.length) {
            container.innerHTML = '<p>لا تتوفر بيانات حالياً.</p>';
            return;
        }
        const body = rows.map(row => {
            const logo = row.team?.logo ? `<img src="${row.team.logo}" alt="${row.team.name || ''}" loading="lazy">` : '';
            return `<tr>
                <td>${row.rank ?? ''}</td>
                <td class="team-name">${logo}${row.team?.name || ''}</td>
                <td>${row.all?.played ?? ''}</td>
                <td>${row.all?.win ?? ''}</td>
                <td>${row.all?.draw ?? ''}</td>
                <td>${row.all?.lose ?? ''}</td>
                <td>${row.points ?? ''}</td>
            </tr>`;
        }).join('');

        container.innerHTML = `<table class="kooragoal-table standings-table">
            <thead>
                <tr>
                    <th>المركز</th>
                    <th>الفريق</th>
                    <th>لعب</th>
                    <th>فوز</th>
                    <th>تعادل</th>
                    <th>خسارة</th>
                    <th>النقاط</th>
                </tr>
            </thead>
            <tbody>${body}</tbody>
        </table>`;
    }

    function fetchStandings(leagueId, container) {
        if (!leagueId || !container) {
            return;
        }
        fetchJSON(`${restBase}/standings/${leagueId}`).then(data => {
            renderStandings(container, data.rows || []);
        }).catch(() => {
            container.innerHTML = '<p>تعذر تحميل الترتيب.</p>';
        });
    }

    function renderScorers(container, rows) {
        if (!container) {
            return;
        }
        if (!rows.length) {
            container.innerHTML = '<p>لا تتوفر بيانات حالياً.</p>';
            return;
        }
        const body = rows.map((row, index) => {
            const player = row.player || {};
            const stats = (row.statistics && row.statistics[0]) || {};
            const team = stats.team || {};
            const photo = player.photo ? `<img src="${player.photo}" alt="${player.name || ''}" loading="lazy">` : '';
            const goals = stats.goals?.total ?? 0;
            const assists = stats.goals?.assists ?? 0;
            return `<tr>
                <td>${index + 1}</td>
                <td class="player-name">${photo}${player.name || ''}</td>
                <td>${team.name || ''}</td>
                <td>${goals}</td>
                <td>${assists}</td>
            </tr>`;
        }).join('');

        container.innerHTML = `<table class="kooragoal-table scorers-table">
            <thead>
                <tr>
                    <th>المركز</th>
                    <th>اللاعب</th>
                    <th>الفريق</th>
                    <th>الأهداف</th>
                    <th>التمريرات الحاسمة</th>
                </tr>
            </thead>
            <tbody>${body}</tbody>
        </table>`;
    }

    function fetchScorers(leagueId, container) {
        if (!leagueId || !container) {
            return;
        }
        fetchJSON(`${restBase}/scorers/${leagueId}`).then(data => {
            renderScorers(container, data.rows || []);
        }).catch(() => {
            container.innerHTML = '<p>تعذر تحميل قائمة الهدافين.</p>';
        });
    }

    function renderEvents(container, events) {
        if (!container) {
            return;
        }
        const list = events.response || [];
        if (!list.length) {
            container.innerHTML = '<p>لا توجد أحداث متاحة لهذه المباراة.</p>';
            return;
        }
        const items = list.map(event => {
            const player = event.player || {};
            const assist = event.assist || {};
            const team = event.team || {};
            const minute = event.time?.elapsed ?? '';
            const icon = `<span class="match-events__icon">${resolveEventIcon(event.type, event.detail)}</span>`;
            const playerImg = player.photo ? `<img src="${player.photo}" alt="${player.name || ''}" loading="lazy">` : '';
            let assistText = '';
            if (assist.name) {
                const template = settings.translations?.assist || 'بمساعدة %s';
                assistText = `<div class="match-events__assist">${template.replace('%s', assist.name)}</div>`;
            }
            return `<li class="match-events__item">
                <div class="match-events__minute">${minute}'</div>
                ${icon}
                <div class="match-events__content">
                    <div class="match-events__player">${playerImg}<span>${player.name || ''}</span></div>
                    ${assistText}
                    <div class="match-events__team">${team.name || ''}</div>
                </div>
            </li>`;
        }).join('');
        container.innerHTML = `<ul class="match-events__list">${items}</ul>`;
    }

    function renderStatistics(container, statistics) {
        if (!container) {
            return;
        }
        const list = statistics.response || [];
        if (list.length < 2) {
            container.innerHTML = '<p>لا توجد إحصائيات متاحة حالياً.</p>';
            return;
        }
        const home = list[0];
        const away = list[1];
        const metrics = {};
        (home.statistics || []).forEach(item => {
            if (!item.type) {
                return;
            }
            metrics[item.type] = { home: item.value ?? 0, away: null };
        });
        (away.statistics || []).forEach(item => {
            if (!item.type) {
                return;
            }
            metrics[item.type] = metrics[item.type] || { home: null, away: 0 };
            metrics[item.type].away = item.value ?? 0;
        });
        const rows = Object.entries(metrics).map(([label, values]) => `<tr>
            <td>${values.home ?? 0}</td>
            <td>${label}</td>
            <td>${values.away ?? 0}</td>
        </tr>`).join('');
        container.innerHTML = `<table class="kooragoal-table statistics-table">
            <thead>
                <tr>
                    <th>${home.team?.name || 'الفريق المضيف'}</th>
                    <th>الإحصائية</th>
                    <th>${away.team?.name || 'الفريق الضيف'}</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
    }

    function renderLineups(container, lineups) {
        if (!container) {
            return;
        }
        const list = lineups.response || [];
        if (!list.length) {
            container.innerHTML = '<p>لم يتم الإعلان عن التشكيلة بعد.</p>';
            return;
        }
        const sections = list.map(lineup => {
            const team = lineup.team || {};
            const coach = lineup.coach?.name ? `<div class="lineup__coach">المدرب: ${lineup.coach.name}</div>` : '';
            const formation = lineup.formation ? `<span class="lineup__formation">${lineup.formation}</span>` : '';
            const players = (lineup.startXI || []).map(slot => {
                const player = slot.player || {};
                const photo = player.photo ? `<img src="${player.photo}" alt="${player.name || ''}" loading="lazy">` : '';
                return `<div class="lineup__player" data-number="${player.number || ''}">
                    ${photo}
                    <span class="lineup__player-number">${player.number || ''}</span>
                    <span class="lineup__player-name">${player.name || ''}</span>
                </div>`;
            }).join('');
            return `<section class="lineup">
                <header class="lineup__header">
                    <div class="lineup__team">
                        ${team.logo ? `<img src="${team.logo}" alt="${team.name || ''}" loading="lazy">` : ''}
                        <div>
                            <h3>${team.name || ''}</h3>
                            ${formation}
                        </div>
                    </div>
                    ${coach}
                </header>
                <div class="lineup__pitch" style="background-image: url('${(settings.themeUrl || '') + '/assets/images/lineup-pitch.png'}');">
                    <div class="lineup__players">${players}</div>
                </div>
            </section>`;
        }).join('');
        container.innerHTML = `<div class="match-lineups">${sections}</div>`;
    }

    function fetchMatchCenterSections(fixtureId, root) {
        if (!fixtureId || !root) {
            return;
        }
        const statsContainer = root.querySelector('#match-statistics');
        const eventsContainer = root.querySelector('#match-events');
        const lineupsContainer = root.querySelector('#match-lineups');

        if (statsContainer) {
            const target = statsContainer.querySelector('[data-fragment="statistics"]') || statsContainer;
            fetchJSON(`${restBase}/match/${fixtureId}/statistics`).then(data => {
                renderStatistics(target, data);
            }).catch(() => {
                target.innerHTML = '<p>تعذر تحميل الإحصائيات.</p>';
            });
        }

        if (eventsContainer) {
            const target = eventsContainer.querySelector('[data-fragment="events"]') || eventsContainer;
            fetchJSON(`${restBase}/match/${fixtureId}/events`).then(data => {
                renderEvents(target, data);
            }).catch(() => {
                target.innerHTML = '<p>تعذر تحميل الأحداث.</p>';
            });
        }

        if (lineupsContainer) {
            const target = lineupsContainer.querySelector('[data-fragment="lineups"]') || lineupsContainer;
            fetchJSON(`${restBase}/match/${fixtureId}/lineups`).then(data => {
                renderLineups(target, data);
            }).catch(() => {
                target.innerHTML = '<p>تعذر تحميل التشكيلات.</p>';
            });
        }
    }

    function initFrontPage() {
        const calendar = document.querySelector('.hero__calendar');
        const fixturesWrapper = document.querySelector('.fixtures__wrapper .fixtures-list');
        const leagueSection = document.querySelector('.league-highlights');

        if (!calendar || !fixturesWrapper) {
            return;
        }

        const state = {
            date: calendar.dataset.currentDate,
            leagues: calendar.dataset.leagues ? calendar.dataset.leagues.split(',').map(Number) : defaultLeagues
        };

        const fixturesContainer = fixturesWrapper;

        function updateCalendarDisplay() {
            calendar.dataset.currentDate = state.date;
            const label = calendar.querySelector('.calendar-display__label');
            const input = calendar.querySelector('.calendar-display__input');
            if (label) {
                label.textContent = state.date;
            }
            if (input) {
                input.value = state.date;
            }
            if (fixturesContainer) {
                fixturesContainer.dataset.currentDate = state.date;
            }
        }

        function changeDate(offset) {
            const current = new Date(`${state.date}T00:00:00Z`);
            current.setUTCDate(current.getUTCDate() + offset);
            state.date = formatDate(current);
            updateCalendarDisplay();
            fetchFixtures(state, fixturesContainer);
        }

        calendar.addEventListener('click', (event) => {
            const button = event.target.closest('[data-direction]');
            if (!button) {
                return;
            }
            event.preventDefault();
            const direction = button.getAttribute('data-direction');
            changeDate(direction === 'next' ? 1 : -1);
        });

        const input = calendar.querySelector('.calendar-display__input');
        if (input) {
            input.addEventListener('change', () => {
                if (input.value && /^\d{4}-\d{2}-\d{2}$/.test(input.value)) {
                    state.date = input.value;
                    updateCalendarDisplay();
                    fetchFixtures(state, fixturesContainer);
                }
            });
        }

        let touchStartX = 0;
        let touchEndX = 0;
        const swipeArea = document.querySelector('[data-swipe-container]');
        if (swipeArea) {
            swipeArea.addEventListener('touchstart', (event) => {
                touchStartX = event.touches[0].clientX;
            });
            swipeArea.addEventListener('touchmove', (event) => {
                touchEndX = event.touches[0].clientX;
            });
            swipeArea.addEventListener('touchend', () => {
                const delta = touchEndX - touchStartX;
                if (Math.abs(delta) > 60) {
                    changeDate(delta < 0 ? 1 : -1);
                }
                touchStartX = touchEndX = 0;
            });
        }

        fetchFixtures(state, fixturesContainer);

        if (leagueSection) {
            const leagueId = Number(leagueSection.dataset.league || 0);
            const standingsContainer = leagueSection.querySelector('[data-fragment="standings"]');
            const scorersContainer = leagueSection.querySelector('[data-fragment="scorers"]');
            fetchStandings(leagueId, standingsContainer);
            fetchScorers(leagueId, scorersContainer);

            window.setInterval(() => {
                fetchStandings(leagueId, standingsContainer);
                fetchScorers(leagueId, scorersContainer);
            }, refreshInterval);
        }

        window.setInterval(() => {
            fetchFixtures(state, fixturesContainer);
        }, refreshInterval);
    }

    function initMatchCenter() {
        const matchRoot = document.querySelector('.match-center');
        if (!matchRoot) {
            return;
        }
        const fixtureId = Number(matchRoot.dataset.fixture || 0);
        if (!fixtureId) {
            return;
        }
        fetchMatchCenterSections(fixtureId, matchRoot);
        window.setInterval(() => {
            fetchMatchCenterSections(fixtureId, matchRoot);
        }, refreshInterval);
    }

    $(document).ready(() => {
        initFrontPage();
        initMatchCenter();
    });
})(jQuery);
