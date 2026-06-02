<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Core_Data
{
    public static function dimensions()
    {
        return array(
            'liberty' => array(
                'label' => __('Liberty', 'american-civic-bestiary'),
                'description' => __('Concern for rights, speech, privacy, autonomy, exit, and due process.', 'american-civic-bestiary'),
            ),
            'order' => array(
                'label' => __('Order', 'american-civic-bestiary'),
                'description' => __('Concern for safety, coordination, enforcement, stability, and institutional authority.', 'american-civic-bestiary'),
            ),
            'equality' => array(
                'label' => __('Equality', 'american-civic-bestiary'),
                'description' => __('Concern for equal dignity, equal protection, access, fairness, and anti-domination.', 'american-civic-bestiary'),
            ),
            'tradition' => array(
                'label' => __('Tradition', 'american-civic-bestiary'),
                'description' => __('Respect for inherited norms, family, religion, continuity, and historical memory.', 'american-civic-bestiary'),
            ),
            'pluralism' => array(
                'label' => __('Pluralism', 'american-civic-bestiary'),
                'description' => __('Comfort with difference, disagreement, diversity, tolerance, and coalition.', 'american-civic-bestiary'),
            ),
            'sovereignty' => array(
                'label' => __('Sovereignty', 'american-civic-bestiary'),
                'description' => __('Concern for self-rule, borders, domestic capacity, and strategic independence.', 'american-civic-bestiary'),
            ),
            'pragmatism' => array(
                'label' => __('Pragmatism', 'american-civic-bestiary'),
                'description' => __('Preference for evidence, workability, tradeoffs, and institutional competence.', 'american-civic-bestiary'),
            ),
            'reform' => array(
                'label' => __('Reform', 'american-civic-bestiary'),
                'description' => __('Desire to change broken systems, redesign incentives, and challenge incumbents.', 'american-civic-bestiary'),
            ),
            'localism' => array(
                'label' => __('Localism', 'american-civic-bestiary'),
                'description' => __('Trust in local knowledge, federalism, community self-rule, and decentralization.', 'american-civic-bestiary'),
            ),
            'globalism' => array(
                'label' => __('Globalism', 'american-civic-bestiary'),
                'description' => __('Recognition of cross-border systems, alliances, planetary risks, and interdependence.', 'american-civic-bestiary'),
            ),
            'solidarity' => array(
                'label' => __('Solidarity', 'american-civic-bestiary'),
                'description' => __('Concern for mutual obligation, public goods, belonging, and a social floor.', 'american-civic-bestiary'),
            ),
            'skepticism' => array(
                'label' => __('Skepticism', 'american-civic-bestiary'),
                'description' => __('Concern about propaganda, donor influence, manipulation, and hidden incentives.', 'american-civic-bestiary'),
            ),
        );
    }

    public static function updown_subfactors()
    {
        return array(
            'traceability' => __('Traceability', 'american-civic-bestiary'),
            'cross_partisan_consistency' => __('Cross-Partisan Consistency', 'american-civic-bestiary'),
            'disclosure_preference' => __('Disclosure Preference', 'american-civic-bestiary'),
            'institutional_causality' => __('Institutional Causality', 'american-civic-bestiary'),
            'narrative_skepticism' => __('Narrative Skepticism', 'american-civic-bestiary'),
            'reform_literacy' => __('Reform Literacy', 'american-civic-bestiary'),
        );
    }

    public static function houses()
    {
        return array(
            'builders' => array(
                'label' => __('Builders', 'american-civic-bestiary'),
                'instinct' => __('Design durable systems', 'american-civic-bestiary'),
                'question' => __('Does it work, and will it last?', 'american-civic-bestiary'),
            ),
            'guardians' => array(
                'label' => __('Guardians', 'american-civic-bestiary'),
                'instinct' => __('Protect what is ours', 'american-civic-bestiary'),
                'question' => __('Is it safe, stable, and ours to keep?', 'american-civic-bestiary'),
            ),
            'pathfinders' => array(
                'label' => __('Pathfinders', 'american-civic-bestiary'),
                'instinct' => __('Break captured systems', 'american-civic-bestiary'),
                'question' => __('Who really benefits, and who decides?', 'american-civic-bestiary'),
            ),
            'weavers' => array(
                'label' => __('Weavers', 'american-civic-bestiary'),
                'instinct' => __('Strengthen the social fabric', 'american-civic-bestiary'),
                'question' => __('Are we still in this together?', 'american-civic-bestiary'),
            ),
        );
    }

    public static function animals()
    {
        $animals = array(
            'beaver' => array(
                'label' => __('Beaver', 'american-civic-bestiary'),
                'title' => __('Institution Builder', 'american-civic-bestiary'),
                'house' => 'builders',
                'motto' => __('Fix the dam before the flood.', 'american-civic-bestiary'),
                'core_question' => __('Are the systems strong enough to carry the public load?', 'american-civic-bestiary'),
                'summary' => __('You see politics as maintenance, repair, implementation, and institutional competence.', 'american-civic-bestiary'),
                'gift' => __('You build and audit the systems that make freedom practical.', 'american-civic-bestiary'),
                'danger' => __('You can defend process after the process has stopped serving the public.', 'american-civic-bestiary'),
                'shadow' => __('Bureaucrat', 'american-civic-bestiary'),
                'capture' => __('Procurement failures, revolving doors, consultant dependency, regulatory hollowing-out, and infrastructure neglect.', 'american-civic-bestiary'),
                'allies' => array('badger', 'bat', 'bison', 'whale'),
                'tensions' => array('coyote', 'fox', 'wild_horse'),
                'corrective' => __('Systems must remain accountable.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 4, 'equality' => 3, 'tradition' => 3, 'pluralism' => 3, 'sovereignty' => 2, 'pragmatism' => 5, 'reform' => 3, 'localism' => 3, 'globalism' => 3, 'solidarity' => 3, 'skepticism' => 2),
            ),
            'badger' => array(
                'label' => __('Badger', 'american-civic-bestiary'),
                'title' => __('Rule Defender', 'american-civic-bestiary'),
                'house' => 'builders',
                'motto' => __('The rules are not decoration.', 'american-civic-bestiary'),
                'core_question' => __('Does power remain bound by law?', 'american-civic-bestiary'),
                'summary' => __('You protect due process, equal enforcement, records, audits, oaths, and rules that restrain arbitrary power.', 'american-civic-bestiary'),
                'gift' => __('You remember that every faction is tempted to exempt itself.', 'american-civic-bestiary'),
                'danger' => __('You can defend a lawful rulebook even when the rulebook itself has been captured.', 'american-civic-bestiary'),
                'shadow' => __('Legalist', 'american-civic-bestiary'),
                'capture' => __('Selective enforcement, dark money, loopholes, judicial politicization, ethics failures, and hidden conflicts.', 'american-civic-bestiary'),
                'allies' => array('beaver', 'fox', 'raccoon', 'otter'),
                'tensions' => array('coyote', 'wolf', 'bison'),
                'corrective' => __('Rules must serve justice, not merely order.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 4, 'order' => 5, 'equality' => 4, 'tradition' => 4, 'pluralism' => 3, 'sovereignty' => 3, 'pragmatism' => 4, 'reform' => 3, 'localism' => 2, 'globalism' => 2, 'solidarity' => 2, 'skepticism' => 4),
            ),
            'bat' => array(
                'label' => __('Bat', 'american-civic-bestiary'),
                'title' => __('Signal Reader', 'american-civic-bestiary'),
                'house' => 'builders',
                'motto' => __('Measure before you moralize.', 'american-civic-bestiary'),
                'core_question' => __('What is the signal, and what is noise?', 'american-civic-bestiary'),
                'summary' => __('You look for data, baselines, incentives, error bars, definitions, and what would change the conclusion.', 'american-civic-bestiary'),
                'gift' => __('You protect public life from informational poisoning.', 'american-civic-bestiary'),
                'danger' => __('You can over-rationalize suffering and miss that pain is sometimes the first signal.', 'american-civic-bestiary'),
                'shadow' => __('Detached Technocrat', 'american-civic-bestiary'),
                'capture' => __('Funded research, bad metrics, algorithmic amplification, think-tank laundering, and misleading dashboards.', 'american-civic-bestiary'),
                'allies' => array('beaver', 'raccoon', 'whale', 'fox'),
                'tensions' => array('bison', 'otter', 'dog'),
                'corrective' => __('Human pain is data too.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 3, 'equality' => 3, 'tradition' => 2, 'pluralism' => 3, 'sovereignty' => 2, 'pragmatism' => 5, 'reform' => 4, 'localism' => 2, 'globalism' => 4, 'solidarity' => 2, 'skepticism' => 5),
            ),
            'whale' => array(
                'label' => __('Whale', 'american-civic-bestiary'),
                'title' => __('Long-View Steward', 'american-civic-bestiary'),
                'house' => 'builders',
                'motto' => __('The future has standing.', 'american-civic-bestiary'),
                'core_question' => __('Will this decision survive the next generation?', 'american-civic-bestiary'),
                'summary' => __('You think in decades and want policy to account for resilience, debt, climate, preparedness, and inherited costs.', 'american-civic-bestiary'),
                'gift' => __('You give the future a seat at the table.', 'american-civic-bestiary'),
                'danger' => __('You can sound abstract to people facing urgent pain now.', 'american-civic-bestiary'),
                'shadow' => __('Oracle', 'american-civic-bestiary'),
                'capture' => __('Short-term extraction, debt gimmicks, fossil lock-in, weak preparedness, and generational cost shifting.', 'american-civic-bestiary'),
                'allies' => array('beaver', 'bat', 'dolphin', 'bison'),
                'tensions' => array('bear', 'moose', 'fox'),
                'corrective' => __('Long-term policy must meet present reality.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 2, 'order' => 4, 'equality' => 4, 'tradition' => 3, 'pluralism' => 4, 'sovereignty' => 2, 'pragmatism' => 4, 'reform' => 4, 'localism' => 2, 'globalism' => 5, 'solidarity' => 4, 'skepticism' => 3),
            ),
            'bear' => array(
                'label' => __('Bear', 'american-civic-bestiary'),
                'title' => __('Homeland Guardian', 'american-civic-bestiary'),
                'house' => 'guardians',
                'motto' => __('A country must be able to stand on its own feet.', 'american-civic-bestiary'),
                'core_question' => __('Can the nation feed, defend, govern, and sustain itself?', 'american-civic-bestiary'),
                'summary' => __('You value sovereignty, secure borders, domestic capacity, strategic independence, and national cohesion.', 'american-civic-bestiary'),
                'gift' => __('You defend the material basis of self-government.', 'american-civic-bestiary'),
                'danger' => __('You can confuse sovereignty with fear or cooperation with dependency.', 'american-civic-bestiary'),
                'shadow' => __('Fortress', 'american-civic-bestiary'),
                'capture' => __('Foreign lobbying, multinational trade capture, offshoring dependency, and strategic infrastructure ownership.', 'american-civic-bestiary'),
                'allies' => array('wolf', 'moose', 'ox', 'wild_horse'),
                'tensions' => array('dolphin', 'otter', 'bison'),
                'corrective' => __('Sovereignty must not become fear.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 4, 'equality' => 2, 'tradition' => 4, 'pluralism' => 2, 'sovereignty' => 5, 'pragmatism' => 3, 'reform' => 3, 'localism' => 3, 'globalism' => 1, 'solidarity' => 3, 'skepticism' => 4),
            ),
            'wolf' => array(
                'label' => __('Wolf', 'american-civic-bestiary'),
                'title' => __('Order Coordinator', 'american-civic-bestiary'),
                'house' => 'guardians',
                'motto' => __('A society that cannot protect itself cannot govern itself.', 'american-civic-bestiary'),
                'core_question' => __('Can the community respond when order breaks?', 'american-civic-bestiary'),
                'summary' => __('You value safety, readiness, enforcement, command structure, and operational competence.', 'american-civic-bestiary'),
                'gift' => __('You make public order real when disorder harms ordinary people.', 'american-civic-bestiary'),
                'danger' => __('You can grant too much permission to coercive power when it promises safety.', 'american-civic-bestiary'),
                'shadow' => __('Authoritarian', 'american-civic-bestiary'),
                'capture' => __('Defense contracting abuse, prison incentives, police impunity, emergency procurement corruption, and security theater.', 'american-civic-bestiary'),
                'allies' => array('bear', 'badger', 'ox', 'beaver'),
                'tensions' => array('fox', 'coyote', 'otter'),
                'corrective' => __('Order needs accountability.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 2, 'order' => 5, 'equality' => 2, 'tradition' => 4, 'pluralism' => 2, 'sovereignty' => 5, 'pragmatism' => 4, 'reform' => 2, 'localism' => 2, 'globalism' => 1, 'solidarity' => 3, 'skepticism' => 3),
            ),
            'ox' => array(
                'label' => __('Ox', 'american-civic-bestiary'),
                'title' => __('Stability Keeper', 'american-civic-bestiary'),
                'house' => 'guardians',
                'motto' => __('Not every inheritance is a chain.', 'american-civic-bestiary'),
                'core_question' => __('What must be preserved for society to remain human?', 'american-civic-bestiary'),
                'summary' => __('You value duty, restraint, family, faith, memory, work, and durable civic habits.', 'american-civic-bestiary'),
                'gift' => __('You protect society from reckless amnesia.', 'american-civic-bestiary'),
                'danger' => __('You can preserve what is familiar even when it is not worthy.', 'american-civic-bestiary'),
                'shadow' => __('Stagnant Gatekeeper', 'american-civic-bestiary'),
                'capture' => __('Hollowed local institutions, donor-colonized civic groups, corporate replacement of local culture, and elite contempt for ordinary life.', 'american-civic-bestiary'),
                'allies' => array('dog', 'bear', 'moose', 'badger'),
                'tensions' => array('coyote', 'dolphin', 'fox'),
                'corrective' => __('Tradition must be tested by truth.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 4, 'equality' => 2, 'tradition' => 5, 'pluralism' => 2, 'sovereignty' => 3, 'pragmatism' => 3, 'reform' => 1, 'localism' => 4, 'globalism' => 1, 'solidarity' => 3, 'skepticism' => 2),
            ),
            'moose' => array(
                'label' => __('Moose', 'american-civic-bestiary'),
                'title' => __('Local Steward', 'american-civic-bestiary'),
                'house' => 'guardians',
                'motto' => __('One size rarely fits all.', 'american-civic-bestiary'),
                'core_question' => __('Who understands the consequences because they live with them?', 'american-civic-bestiary'),
                'summary' => __('You trust local knowledge, regional variation, town halls, counties, cooperatives, and community accountability.', 'american-civic-bestiary'),
                'gift' => __('You keep democracy close enough to touch.', 'american-civic-bestiary'),
                'danger' => __('You can romanticize local power even when local power is captured too.', 'american-civic-bestiary'),
                'shadow' => __('Parish Boss', 'american-civic-bestiary'),
                'capture' => __('Utility monopolies, hospital cartels, land-use capture, outside ownership, state preemption, and national PAC money in local races.', 'american-civic-bestiary'),
                'allies' => array('dog', 'bear', 'fox', 'ox'),
                'tensions' => array('dolphin', 'beaver', 'bison'),
                'corrective' => __('Localism must recognize scale.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 4, 'order' => 3, 'equality' => 2, 'tradition' => 4, 'pluralism' => 3, 'sovereignty' => 4, 'pragmatism' => 4, 'reform' => 3, 'localism' => 5, 'globalism' => 1, 'solidarity' => 3, 'skepticism' => 4),
            ),
            'fox' => array(
                'label' => __('Fox', 'american-civic-bestiary'),
                'title' => __('Liberty Strategist', 'american-civic-bestiary'),
                'house' => 'pathfinders',
                'motto' => __('Never give power a blank check.', 'american-civic-bestiary'),
                'core_question' => __('What stops this power from being abused later?', 'american-civic-bestiary'),
                'summary' => __('You are skeptical of concentrated power wherever it appears and value speech, privacy, exit, competition, and limits.', 'american-civic-bestiary'),
                'gift' => __('You preserve liberty before emergencies erase it.', 'american-civic-bestiary'),
                'danger' => __('You can underestimate the institutions needed to defend freedom.', 'american-civic-bestiary'),
                'shadow' => __('Cynic', 'american-civic-bestiary'),
                'capture' => __('State-corporate collusion, censorship pressure, surveillance, antitrust failure, licensing cartels, and platform power.', 'american-civic-bestiary'),
                'allies' => array('raccoon', 'wild_horse', 'badger', 'moose'),
                'tensions' => array('wolf', 'bison', 'beaver'),
                'corrective' => __('Liberty sometimes needs institutions.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 5, 'order' => 2, 'equality' => 3, 'tradition' => 2, 'pluralism' => 4, 'sovereignty' => 3, 'pragmatism' => 4, 'reform' => 4, 'localism' => 4, 'globalism' => 2, 'solidarity' => 2, 'skepticism' => 5),
            ),
            'coyote' => array(
                'label' => __('Coyote', 'american-civic-bestiary'),
                'title' => __('Disruptive Reformer', 'american-civic-bestiary'),
                'house' => 'pathfinders',
                'motto' => __('A broken system does not deserve gentle handling.', 'american-civic-bestiary'),
                'core_question' => __('What must be disrupted because normal reform has failed?', 'american-civic-bestiary'),
                'summary' => __('You see captured systems that use procedure, civility, and complexity to delay accountability.', 'american-civic-bestiary'),
                'gift' => __('You break the spell of inevitability.', 'american-civic-bestiary'),
                'danger' => __('You can turn disruption into identity and mistake noise for change.', 'american-civic-bestiary'),
                'shadow' => __('Arsonist', 'american-civic-bestiary'),
                'capture' => __('Donor-class control, party duopoly, ballot barriers, closed primaries, gerrymanders, and incumbent cartels.', 'american-civic-bestiary'),
                'allies' => array('raccoon', 'wild_horse', 'bison', 'fox'),
                'tensions' => array('ox', 'beaver', 'badger'),
                'corrective' => __('Disruption is not automatically reform.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 4, 'order' => 1, 'equality' => 4, 'tradition' => 1, 'pluralism' => 3, 'sovereignty' => 3, 'pragmatism' => 3, 'reform' => 5, 'localism' => 3, 'globalism' => 2, 'solidarity' => 3, 'skepticism' => 5),
            ),
            'wild_horse' => array(
                'label' => __('Wild Horse', 'american-civic-bestiary'),
                'title' => __('Independent Populist', 'american-civic-bestiary'),
                'house' => 'pathfinders',
                'motto' => __('No machine owns me.', 'american-civic-bestiary'),
                'core_question' => __('Who is trying to script my choices?', 'american-civic-bestiary'),
                'summary' => __('You distrust party labels, managed binaries, purity tests, and algorithmic outrage cycles.', 'american-civic-bestiary'),
                'gift' => __('You keep democracy from becoming managed performance.', 'american-civic-bestiary'),
                'danger' => __('You can become contrarian enough to be manipulated through a different door.', 'american-civic-bestiary'),
                'shadow' => __('Untethered Contrarian', 'american-civic-bestiary'),
                'capture' => __('Two-party control, media manipulation, consultant politics, controlled opposition, donor-approved candidates, and manufactured consensus.', 'american-civic-bestiary'),
                'allies' => array('fox', 'raccoon', 'bear', 'coyote'),
                'tensions' => array('beaver', 'otter', 'badger'),
                'corrective' => __('Independence requires discipline.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 5, 'order' => 2, 'equality' => 3, 'tradition' => 3, 'pluralism' => 3, 'sovereignty' => 4, 'pragmatism' => 3, 'reform' => 4, 'localism' => 3, 'globalism' => 2, 'solidarity' => 3, 'skepticism' => 5),
            ),
            'raccoon' => array(
                'label' => __('Raccoon', 'american-civic-bestiary'),
                'title' => __('Corruption Spotter', 'american-civic-bestiary'),
                'house' => 'pathfinders',
                'motto' => __('Show me the receipts.', 'american-civic-bestiary'),
                'core_question' => __('Who paid, who profits, and who is hiding the trail?', 'american-civic-bestiary'),
                'summary' => __('You ask about ownership, donor networks, procurement data, lobbying filings, shell entities, and who benefits.', 'american-civic-bestiary'),
                'gift' => __('You make hidden power legible.', 'american-civic-bestiary'),
                'danger' => __('You can let suspicion outrun evidence.', 'american-civic-bestiary'),
                'shadow' => __('Conspiracist', 'american-civic-bestiary'),
                'capture' => __('Dark money, shell companies, donor networks, nonprofit pass-throughs, astroturf campaigns, procurement fraud, and think-tank laundering.', 'american-civic-bestiary'),
                'allies' => array('fox', 'bat', 'badger', 'coyote'),
                'tensions' => array('otter', 'beaver', 'dog'),
                'corrective' => __('Evidence must outrank suspicion.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 4, 'order' => 3, 'equality' => 4, 'tradition' => 2, 'pluralism' => 3, 'sovereignty' => 3, 'pragmatism' => 4, 'reform' => 5, 'localism' => 3, 'globalism' => 2, 'solidarity' => 3, 'skepticism' => 5),
            ),
            'otter' => array(
                'label' => __('Otter', 'american-civic-bestiary'),
                'title' => __('Bridgebuilder', 'american-civic-bestiary'),
                'house' => 'weavers',
                'motto' => __('Many ways of life, one shared river.', 'american-civic-bestiary'),
                'core_question' => __('Can we still govern together despite disagreement?', 'american-civic-bestiary'),
                'summary' => __('You value tolerance, de-escalation, coalition, civic friendship, religious liberty, and viewpoint diversity.', 'american-civic-bestiary'),
                'gift' => __('You keep disagreement from becoming permanent enemyhood.', 'american-civic-bestiary'),
                'danger' => __('You can avoid necessary confrontation in the name of harmony.', 'american-civic-bestiary'),
                'shadow' => __('Appeaser', 'american-civic-bestiary'),
                'capture' => __('Outrage media, gerrymandered incentives, primary extremism, algorithmic conflict, and donor-funded culture war.', 'american-civic-bestiary'),
                'allies' => array('dog', 'dolphin', 'badger', 'bison'),
                'tensions' => array('wolf', 'coyote', 'raccoon'),
                'corrective' => __('Peace cannot replace accountability.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 4, 'order' => 3, 'equality' => 4, 'tradition' => 3, 'pluralism' => 5, 'sovereignty' => 2, 'pragmatism' => 4, 'reform' => 3, 'localism' => 3, 'globalism' => 3, 'solidarity' => 4, 'skepticism' => 2),
            ),
            'bison' => array(
                'label' => __('Bison', 'american-civic-bestiary'),
                'title' => __('Social Floor Guardian', 'american-civic-bestiary'),
                'house' => 'weavers',
                'motto' => __('No herd survives by abandoning its weakest.', 'american-civic-bestiary'),
                'core_question' => __('What must no person be allowed to fall below?', 'american-civic-bestiary'),
                'summary' => __('You demand a real social floor beneath which dignity is not allowed to collapse.', 'american-civic-bestiary'),
                'gift' => __('You make solidarity concrete.', 'american-civic-bestiary'),
                'danger' => __('You can underweight implementation, incentives, cost, and state capacity.', 'american-civic-bestiary'),
                'shadow' => __('Paternalist', 'american-civic-bestiary'),
                'capture' => __('Healthcare middlemen, monopoly pricing, private prisons, predatory finance, subsidy capture, and contractor extraction.', 'american-civic-bestiary'),
                'allies' => array('dog', 'otter', 'beaver', 'whale'),
                'tensions' => array('fox', 'bear', 'wolf'),
                'corrective' => __('Solidarity must survive implementation.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 3, 'equality' => 5, 'tradition' => 2, 'pluralism' => 4, 'sovereignty' => 2, 'pragmatism' => 3, 'reform' => 4, 'localism' => 3, 'globalism' => 3, 'solidarity' => 5, 'skepticism' => 3),
            ),
            'dog' => array(
                'label' => __('Dog', 'american-civic-bestiary'),
                'title' => __('Civic Neighbor', 'american-civic-bestiary'),
                'house' => 'weavers',
                'motto' => __('A republic is something you practice.', 'american-civic-bestiary'),
                'core_question' => __('Are people still connected enough to govern themselves?', 'american-civic-bestiary'),
                'summary' => __('You value volunteering, associations, juries, local meetings, mutual aid, voting, coaching, and showing up.', 'american-civic-bestiary'),
                'gift' => __('You turn citizenship into daily practice.', 'american-civic-bestiary'),
                'danger' => __('You can confuse familiar belonging with broader justice.', 'american-civic-bestiary'),
                'shadow' => __('Club Loyalist', 'american-civic-bestiary'),
                'capture' => __('Local news collapse, national PACs in school boards, outsourced civic institutions, partisan colonization, and hollowed clubs.', 'american-civic-bestiary'),
                'allies' => array('moose', 'otter', 'ox', 'bison'),
                'tensions' => array('raccoon', 'coyote', 'dolphin'),
                'corrective' => __('Loyalty must remain honest.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 3, 'equality' => 3, 'tradition' => 4, 'pluralism' => 4, 'sovereignty' => 3, 'pragmatism' => 4, 'reform' => 2, 'localism' => 5, 'globalism' => 2, 'solidarity' => 5, 'skepticism' => 2),
            ),
            'dolphin' => array(
                'label' => __('Dolphin', 'american-civic-bestiary'),
                'title' => __('Global Humanist', 'american-civic-bestiary'),
                'house' => 'weavers',
                'motto' => __('No nation swims alone.', 'american-civic-bestiary'),
                'core_question' => __('What problems require cooperation beyond borders?', 'american-civic-bestiary'),
                'summary' => __('You see climate, pandemics, supply chains, cyber threats, oceans, migration, and finance as cross-border realities.', 'american-civic-bestiary'),
                'gift' => __('You widen the moral and strategic field of vision.', 'american-civic-bestiary'),
                'danger' => __('You can underweight democratic legitimacy, local consent, and national identity.', 'american-civic-bestiary'),
                'shadow' => __('Rootless Manager', 'american-civic-bestiary'),
                'capture' => __('Multinational lobbying, opaque treaties, NGO-industrial incentives, global regulatory arbitrage, and international institutional capture.', 'american-civic-bestiary'),
                'allies' => array('whale', 'otter', 'bison', 'bat'),
                'tensions' => array('bear', 'moose', 'ox'),
                'corrective' => __('Globalism needs democratic legitimacy.', 'american-civic-bestiary'),
                'centroids' => array('liberty' => 3, 'order' => 3, 'equality' => 5, 'tradition' => 2, 'pluralism' => 5, 'sovereignty' => 1, 'pragmatism' => 4, 'reform' => 4, 'localism' => 2, 'globalism' => 5, 'solidarity' => 5, 'skepticism' => 3),
            ),
        );

        foreach ($animals as $key => $animal) {
            $animals[$key]['key'] = $key;
            $animals[$key]['centroids'] = self::convert_centroids($animal['centroids']);
        }

        return $animals;
    }

    public static function settings_defaults()
    {
        return array(
            'minimum_questions' => 10,
            'questions_per_session' => 10,
            'show_email_field' => true,
            'show_name_field' => true,
            'consent_text' => __('I understand this profile is educational and reflective. It is not a scientific diagnosis, voting guide, party affiliation test, or official political classification.', 'american-civic-bestiary'),
            'retain_anonymous_days' => 0,
            'assessment_eyebrow' => __('American Civic Bestiary', 'american-civic-bestiary'),
            'assessment_title' => __('Answer the next civic scenarios', 'american-civic-bestiary'),
            'assessment_intro' => __('Work through the next set of scenarios to sharpen and refine the profile.', 'american-civic-bestiary'),
            'dashboard_eyebrow' => __('Your civic animal profile', 'american-civic-bestiary'),
            'dashboard_intro' => '',
            'dashboard_outro' => '',
            'report_layout_mode' => 'auto',
            'report_max_width' => '',
            'report_position' => 'before_form',
            'refine_display' => 'button',
            'allow_retakes' => false,
            'show_icons' => true,
            'show_house_scores' => true,
            'show_capture_overlay' => true,
            'show_dimension_bars' => true,
            'show_primary_secondary_cards' => true,
            'compact_mode' => false,
            'top_match_count' => 8,
            'cta_label' => '',
            'cta_url' => '',
            'inherit_theme_styles' => true,
            'accent_color' => '',
            'accent_color_secondary' => '',
            'surface_color' => '',
            'panel_color' => '',
            'text_color' => '',
            'muted_color' => '',
            'border_radius' => 16,
            'panel_shadow' => 'medium',
            'custom_css' => '',
        );
    }

    private static function convert_centroids(array $centroids)
    {
        $converted = array();
        foreach ($centroids as $dimension => $value) {
            $converted[$dimension] = ((float) $value - 1.0) * 25.0;
        }

        return $converted;
    }
}
