<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

return [
    'play_more' => 'Может, лучше в osu! поиграете?',
    'require_login' => 'Войдите, чтобы оставить своё мнение',
    'require_verification' => 'Пожалуйста, подтвердите свой аккаунт.',
    'restricted' => "Это нельзя сделать, пока ваши права ограничены.",
    'silenced' => "Это сделать нельзя, пока Вы заглушены.",
    'unauthorized' => 'Доступ запрещён.',

    'beatmap_discussion' => [
        'destroy' => [
            'is_hype' => 'Нельзя отменить хайп.',
            'has_reply' => 'Нельзя удалить обсуждение с ответами',
        ],
        'nominate' => [
            'exhausted' => 'Вы достигли своего лимита номинаций за день, повторите попытку завтра.',
            'incorrect_state' => 'Произошла ошибка при выполнении этого действия, попробуйте обновить страницу.',
            'owner' => "Нельзя номинировать свою карту.",
            'set_metadata' => 'Вы должны указать жанр и язык карты перед её номинированием.',
        ],
        'resolve' => [
            'not_owner' => 'Решить обсуждение может только его создатель или автор карты.',
        ],

        'store' => [
            'mapper_note_wrong_user' => 'Только автор карты или номинатор/член NAT может оставлять заметки.',
        ],

        'vote' => [
            'bot' => "Нельзя проголосовать за пост от бота",
            'limit_exceeded' => 'Вы голосуете слишком часто, пожалуйста, немного подождите',
            'owner' => "Нельзя голосовать в собственном обсуждении.",
            'wrong_beatmapset_state' => 'Можно голосовать только в обсуждениях карт на рассмотрении.',
        ],
    ],

    'beatmap_discussion_post' => [
        'destroy' => [
            'not_owner' => 'Вы можете удалять только свои посты.',
            'resolved' => 'Вы не можете удалить пост в решённом обсуждении.',
            'system_generated' => 'Нельзя удалить автоматически созданный пост.',
        ],

        'edit' => [
            'not_owner' => 'Редактировать пост может только его автор.',
            'resolved' => 'Нельзя отредактировать пост в решённом обсуждении.',
            'system_generated' => 'Нельзя редактировать автоматически созданный пост.',
        ],
    ],

    'beatmapset' => [
        'discussion_locked' => 'Возможность обсуждения этой карты закрыта.',

        'metadata' => [
            'nominated' => 'Вы не можете изменить метаданные номинируемой карты. В случае ошибок свяжитесь с номнатором или членом NAT.',
        ],
    ],

    'chat' => [
        'annnonce_only' => 'Этот канал предназначен только для объявлений.',
        'blocked' => 'Вы не можете отправить сообщение пользователю, который заблокировал вас или которого заблокировали вы.',
        'friends_only' => 'Этот пользователь блокирует сообщения от всех, кроме друзей.',
        'moderated' => 'Этот канал доступен только модераторам.',
        'no_access' => 'У вас нет доступа к этому каналу.',
        'receive_friends_only' => 'Пользователь может не ответить, потому что вы принимаете сообщения только от людей из вашего списка друзей.',
        'restricted' => 'Нельзя отправлять сообщения, пока вы заглушены, вас ограничили или забанили.',
        'silenced' => 'Нельзя отправлять сообщения, пока вы заглушены, вас ограничили или забанили.',
    ],

    'comment' => [
        'store' => [
            'disabled' => 'Комментарии отключены',
        ],
        'update' => [
            'deleted' => "Нельзя редактировать удалённый пост.",
        ],
    ],

    'contest' => [
        'voting_over' => 'Вы не можете изменить свой голос после окончания периода голосования.',

        'entry' => [
            'limit_reached' => 'Вы исчерпали количество заявок для этого конкурса',
            'over' => 'Благодарим за ваши заявки на участие в этом конкурсе! Голосование начнется в ближайшее время.',
        ],
    ],

    'forum' => [
        'moderate' => [
            'no_permission' => 'У вас нет прав на модерацию этого форума.',
        ],

        'post' => [
            'delete' => [
                'only_last_post' => 'Удалить можно только самый последний пост.',
                'locked' => 'Нельзя удалить пост в закрытой теме.',
                'no_forum_access' => 'Необходим доступ к запрашиваемому форуму.',
                'not_owner' => 'Удалить пост может только его автор.',
            ],

            'edit' => [
                'deleted' => 'Нельзя редактировать удалённый пост.',
                'locked' => 'Пост защищён от изменений.',
                'no_forum_access' => 'Необходим доступ к запрашиваемому форуму.',
                'not_owner' => 'Редактировать пост может только его автор.',
                'topic_locked' => 'Нельзя редактировать пост в закрытой теме.',
            ],

            'store' => [
                'play_more' => 'Поиграйте в игру, прежде чем писать что-либо на форуме. Если у вас имеются проблемы с игрой, попробуйте написать об этом на форуме «Помощи и поддержки».',
                'too_many_help_posts' => "Вы должны поиграть в игру подольше, прежде чем создавать дополнительные посты. Если у вас все ещё имеются проблемы с игрой, напишите на почту support@ppy.sh", // FIXME: unhardcode email address.
            ],
        ],

        'topic' => [
            'reply' => [
                'double_post' => 'Пожалуйста, отредактируйте ваш последний пост, не создавайте новый.',
                'locked' => 'Нельзя ответить в закрытой теме.',
                'no_forum_access' => 'Необходим доступ к запрашиваемому форуму.',
                'no_permission' => 'Для ответа недостаточно прав.',

                'user' => [
                    'require_login' => 'Чтобы ответить, войдите в аккаунт.',
                    'restricted' => "Нельзя отвечать, когда аккаунт ограничен.",
                    'silenced' => "Нельзя отвечать, пока у Вы заглушены.",
                ],
            ],

            'store' => [
                'no_forum_access' => 'Необходим доступ к запрашиваемому форуму.',
                'no_permission' => 'Нет прав на создание новой темы.',
                'forum_closed' => 'Форум закрыт.',
            ],

            'vote' => [
                'no_forum_access' => 'Необходим доступ к запрашиваемому форуму.',
                'over' => 'Опрос окончен и проголосовать в нём уже нельзя.',
                'play_more' => 'Перед тем, как голосовать, нужно поиграть подольше.',
                'voted' => 'Нельзя изменить свой голос.',

                'user' => [
                    'require_login' => 'Чтобы проголосовать, войдите в аккаунт.',
                    'restricted' => "Нельзя голосовать, когда аккаунт ограничен.",
                    'silenced' => "Нельзя голосовать, пока у вы заглушены.",
                ],
            ],

            'watch' => [
                'no_forum_access' => 'Необходим доступ к запрашиваемому форуму.',
            ],
        ],

        'topic_cover' => [
            'edit' => [
                'uneditable' => 'Указана неверная обложка.',
                'not_owner' => 'Только автор темы может менять обложку.',
            ],
            'store' => [
                'forum_not_allowed' => 'В этом форуме нельзя настраивать обложки.',
            ],
        ],

        'view' => [
            'admin_only' => 'Только администратор может просматривать этот форум.',
        ],
    ],

    'score' => [
        'pin' => [
            'disabled_type' => "Этот вид рекорда закрепить нельзя",
            'not_owner' => 'Только владелец рекорда может его закрепить.',
            'too_many' => 'Закреплено слишком много рекордов.',
        ],
    ],

    'user' => [
        'page' => [
            'edit' => [
                'locked' => 'Страница заблокирована.',
                'not_owner' => 'Вы можете редактировать только собственную страницу.',
                'require_supporter_tag' => 'Необходим osu!supporter.',
            ],
        ],
        'update_email' => [
            'locked' => 'адрес электронной почты заблокирован от изменений',
        ],
    ],
];
