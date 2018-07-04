<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use App\ScheduledMessage;

class ScheduledMessageController extends Controller
{
    function index () {
        $scheduled_messages = ScheduledMessage::orderBy('created_at', 'desc')->get();
        return view('admin.scheduled_messages.index', [
            'scheduled_messages' => $scheduled_messages,
        ]);
    }

    function new () {
        $scheduled_message = new ScheduledMessage();
        return view('admin.scheduled_messages.new', [
            'scheduled_message' => $scheduled_message,
        ]);
    }

    function create (Request $request) {
        $validator = Validator::make($request->all(), [
            'body' => 'required|max:260',
            'send_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect('/admin/scheduled_messages/new')
                ->withErrors($validator)
                ->withInput();
        }

        ScheduledMessage::create([
            'body' => $request->input('body'),
            'send_at' => $request->input('send_at'),
        ]);

        return redirect('/admin/scheduled_messages');
    }

    function edit (ScheduledMessage $scheduled_message, Request $request) {
        if ($scheduled_message->sent) {
            session('status', 'Cannot change already sent message');
            return redirect('/admin/scheduled_messages');
        }

        return view('admin.scheduled_messages.edit', [
            'scheduled_message' => $scheduled_message,
        ]);
    }

    function update (ScheduledMessage $scheduled_message, Request $request) {
        if ($scheduled_message->sent) {
            $errors = new MessageBag();
            $errors->add('cant_change_sent_message', 'Cannot change already sent message');
            return redirect('/admin/scheduled_messages')->withErrors($errors);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|max:260',
            'send_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect('/admin/scheduled_messages/' . $scheduled_message->id)
                ->withErrors($validator);
        }

        $scheduled_message->body = $request->input('body');
        $scheduled_message->send_at = $request->input('send_at');
        $scheduled_message->save();

        return redirect('/admin/scheduled_messages');
    }

    function destroy (ScheduledMessage $scheduled_message, Request $request) {
        if ($scheduled_message->sent) {
            $errors = new MessageBag();
            $errors->add('cant_change_sent_message', 'Cannot change already sent message');
            return redirect('/admin/scheduled_messages')->withErrors($errors);
        }

        $scheduled_message->delete();
        session('status', 'Message deleted.');
        return redirect('/admin/scheduled_messages');
    }
}
