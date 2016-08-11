<?hh // strict
namespace PayPal\Controllers

use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;

class ContentController extends Controller
{
  public function getPayPalExpressButton(Twig $twig):string
  {
    return $twig->render('PayPal::content.expressbutton');
  }
}
