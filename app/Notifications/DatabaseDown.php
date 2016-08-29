<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DatabaseDown extends Notification
{
    use Queueable;

    protected $msg;

    /**
     * @param string $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Create a new notification instance.
     *
     * @param string $msg
     */
    public function __construct($msg='')
    {
        $this->msg = $msg;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if(config('app.env')=='production')
            return ['mail', 'nexmo'];
        else
            return ['mail'];
//        return ['mail', 'nexmo'];
//        return $notifiable->prefers_sms ? ['nexmo'] : ['mail', 'database'];
//        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject('Database Down')
            ->from('ilhanet.lan@gmail.com', 'Delivery24Horas')
            ->line('Erro constatado no Banco de dados em '.env('DB_HOST', 'localhost').'.')
            ->line('Mensagem: '.$this->msg)
            ->action('Verificar o Site', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the Nexmo / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->from(env('NEXMO_NUMBER'))
            ->content('Erro constatado no Banco de dados em '.env('DB_HOST', 'localhost').'. Mensagem: '.$this->msg);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function scheduled(){
        return function () {
            $user = new \App\User([
                'name'=>'Luciano',
                'phone_number'=>'5522988194655',
                'email'=>'luciano.bapo@gmail.com',
            ]);

            try {
                $database_host = config('database.connections.mysql.host');
                $database_name = config('database.connections.mysql.database');
                $database_user = config('database.connections.mysql.username');
                $database_password = config('database.connections.mysql.password');

                $connection = mysqli_connect($database_host,$database_user,$database_password,$database_name);

                if ($err = mysqli_connect_errno()){
                    $msg = $err.': '.mysqli_connect_error ();
                    $user->notify(new \App\Notifications\DatabaseDown($msg));
                } else {
//                dd($user);
//                \Illuminate\Support\Facades\Notification::send($user, new \App\Notifications\DatabaseDown());
                    $msg = 'no error occurred';
                    $user->notify(new \App\Notifications\DatabaseDown($msg));
                }
            } catch (\Exception $e){
                $msg = 'Erro: '.$e->getMessage();
                $user->notify(new \App\Notifications\DatabaseDown($msg));
            }
        };

    }
}
