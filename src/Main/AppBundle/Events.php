<?php

namespace Main\AppBundle;

final class Events
{
    const TICKET_PRE_CREATE = 'ticket.pre.create';
    const TICKET_POST_CREATE = 'ticket.post.create';

    const TICKET_PRE_RESPONSE_ADD = 'ticket.pre.response.add';
    const TICKET_POST_RESPONSE_ADD = 'ticket.post.response.add';

    const TICKET_ASSIGNED = 'ticket.assigned';
    const TICKET_UNASSIGNED = 'ticket.unassigned';
    const TICKET_FAVORED = 'ticket.favored';
    const TICKET_UNFAVORED = 'ticket.unfavored';
    const TICKET_OBSERVED = 'ticket.observed';
    const TICKET_UNOBSERVED = 'ticket.unobserved';

    const TICKET_SUPPORTER_INVITED = 'ticket.supporter.invited';

    const TICKET_PRE_CLOSED = 'ticket.pre.closed';
    const TICKET_POST_CLOSED = 'ticket.post.closed';

    const TICKET_UPDATED = 'ticket.updated';
    const TICKET_FORWARDED = 'ticket.forwarded';
}
