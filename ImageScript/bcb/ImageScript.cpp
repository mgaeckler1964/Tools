//---------------------------------------------------------------------------

#include <vcl.h>
#pragma hdrstop
USERES("ImageScript.res");
USEFORM("ISmain.cpp", IsMainForm);
USELIB("..\..\..\..\..\object\gaklib\gaklib_bcb.lib");
//---------------------------------------------------------------------------
WINAPI WinMain(HINSTANCE, HINSTANCE, LPSTR, int)
{
	try
	{
		Application->Initialize();
		Application->CreateForm(__classid(TIsMainForm), &IsMainForm);
		Application->Run();
	}
	catch (Exception &exception)
	{
		Application->ShowException(&exception);
	}
	return 0;
}
//---------------------------------------------------------------------------
