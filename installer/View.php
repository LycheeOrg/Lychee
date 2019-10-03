<?php /** @noinspection PhpIncludeInspection */


namespace Installer;


use Template;

class View
{

	private function head($title, $step){
echo '<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Lychee Installer</title>
        <link rel="icon" type="image/png" href="installer/assets/img/favicon/favicon-16x16.png" sizes="16x16"/>
        <link rel="icon" type="image/png" href="installer/assets/img/favicon/favicon-32x32.png" sizes="32x32"/>
        <link rel="icon" type="image/png" href="installer/assets/img/favicon/favicon-96x96.png" sizes="96x96"/>
        <link href="installer/assets/css/style.min.css" rel="stylesheet"/>
        
        <!-- @yield(\'style\') -->
    </head>
    <body>
        <div class="master">
            <div class="box">
                <div class="header">
                    <h1 class="header__title">'.$title.'</h1>
                </div>
                <ul class="step">
                    <li class="step__divider"></li>
                    <li class="step__item '.($step == 'Migrate' ? 'active' : '').'">';
//				if ($step == 'env') {
					echo'<i class="step__icon fa fa-server" aria-hidden="true"></i>';
//				}
				echo '
                    </li>';
					echo '
                    <li class="step__divider"></li>
                    <li class="step__item '.($step == 'Env'? 'active' :'').'">';
                    if ($step == 'Env')
                    {
						echo '<a href="?step=env">
                                <i class="step__icon fa fa-cog" aria-hidden="true"></i>
                            </a>';
                    }
                    else{
	                    echo '<i class="step__icon fa fa-cog" aria-hidden="true"></i>';
                    }
                    echo '</li>
                    <li class="step__divider"></li>
                    <li class="step__item '.($step == 'Permissions'? 'active' :'').'">';

					if ($step == 'Permissions'){
						echo '<a href="?step=perm"><i class="step__icon fa fa-key" aria-hidden="true"></i></a>';
					}
					else{
						echo '<i class="step__icon fa fa-key" aria-hidden="true"></i>';
					};
					echo '
                    </li>
                    <li class="step__divider"></li>
                    <li class="step__item '.($step == 'Requirements'? 'active' :'').'">';

					if ($step == 'Requirements'){
						echo '<a href="?step=req"><i class="step__icon fa fa-list" aria-hidden="true"></i></a>';
					}
					else{
						echo '<i class="step__icon fa fa-list" aria-hidden="true"></i>';
					};

                    echo '</li>
                    <li class="step__divider"></li>
                    <li class="step__item '.($step == 'Welcome'? 'active' :'').'">';
					if ($step == 'Welcome'){
						echo '<a href="?step="><i class="step__icon fa fa-home" aria-hidden="true"></i></a>';
					}
					else{
						echo '<i class="step__icon fa fa-home" aria-hidden="true"></i>';
					};

					echo '
                    </li>
                    <li class="step__divider"></li>
                </ul>
                <div class="main">';
	}

	private function tail(){
echo '                </div>
            </div>
        </div>
    </body>
</html>';
	}

	public function apply(string $view_name, array $inputs)
	{

		$template_name = __NAMESPACE__ . '\\Templates\\' .$view_name;
		/** @var Template $template */
		$template = new $template_name();

		$this->head('Lychee-installer',$view_name);
		$template->print($inputs);
		$this->tail();
	}

}