/**
 *    Copyright 2015-2018 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

import * as React from 'react';
import { ChatMessageSendAction } from 'actions/chat-actions';
import { inject } from 'mobx-react';
import Message from 'models/chat/message';

@inject('dataStore')
@inject('dispatcher')
export default class ChatInput extends React.Component<any, any> {
  sendMessage(target) {
    if (target.value == '')
      return

    let message: Message = new Message();
    message.sender = this.props.dataStore.userStore.getOrCreate(currentUser.id);
    message.channel = this.props.dataStore.channelStore.getOrCreate(this.props.dataStore.uiState.chat.selected);
    message.content = target.value;

    this.props.dispatcher.dispatch(new ChatMessageSendAction(message));
    target.value = ''
  }

  buttonClicked = (e) => {
    this.sendMessage($(e.currentTarget).parent().children('input')[0])
  }

  checkIfEnterPressed = (e) => {
    if (e.keyCode == 13)
      this.sendMessage($(e.currentTarget)[0])
  }

  render(): React.ReactNode {
    let classes = 'messaging__chat-input'
    if (this.props.disabled)
      classes += ' messaging__chat-input--disabled'

    return (
      <div className={classes}>
        <input className='messaging__chat-input-box'
          name= 'textbox'
          placeholder={this.props.disabled ? 'unable to send message...' : osu.trans('messages.input-placeholder')}
          onKeyDown={this.checkIfEnterPressed}
          disabled={this.props.disabled}
        />

        <BigButton
          text={osu.trans('messages.reply')}
          icon='fas fa-reply'
          modifiers={['chat-send']}
          props={{
            onClick: this.buttonClicked,
            disabled: this.props.disabled
          }}
        />
      </div>
    )
  }
}
