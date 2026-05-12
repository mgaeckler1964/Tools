//---------------------------------------------------------------------------

#ifndef ISmainH
#define ISmainH
//---------------------------------------------------------------------------
#include <Classes.hpp>
#include <Controls.hpp>
#include <StdCtrls.hpp>
#include <Forms.hpp>
#include <ExtCtrls.hpp>
#include <Dialogs.hpp>
#include <ExtDlgs.hpp>
#include <FileCtrl.hpp>
#include <Buttons.hpp>
//---------------------------------------------------------------------------
class TIsMainForm : public TForm
{
__published:	// IDE-managed Components
	TSplitter *Splitter1;
	TPanel *Panel2;
	TDriveComboBox *DriveComboBox;
	TDirectoryListBox *DirectoryListBox;
	TSplitter *Splitter2;
	TPanel *PanelImageViewer;
	TImage *Image;
	TPanel *Panel1;
	TListBox *ListBoxFiles;
	TPanel *Panel3;
	TSpeedButton *SpeedButtonUp;
	TSpeedButton *SpeedButtonDown;
	TSpeedButton *SpeedButtonSave;
	TSpeedButton *SpeedButtonRemove;
	TSpeedButton *SpeedButtonUndo;
	void __fastcall ListBoxFilesClick(TObject *Sender);
	void __fastcall FormCreate(TObject *Sender);
	void __fastcall DirectoryListBoxChange(TObject *Sender);
	void __fastcall PanelImageViewerResize(TObject *Sender);
	void __fastcall FormShow(TObject *Sender);
	void __fastcall SpeedButtonUpClick(TObject *Sender);
	void __fastcall SpeedButtonDownClick(TObject *Sender);
	void __fastcall FormClose(TObject *Sender, TCloseAction &Action);
	void __fastcall SpeedButtonSaveClick(TObject *Sender);
	void __fastcall SpeedButtonRemoveClick(TObject *Sender);
	void __fastcall SpeedButtonUndoClick(TObject *Sender);
private:	// User declarations
	bool changedFlag;
	AnsiString	configFile;
	void save( void );
	void setCaption( void )
	{
		AnsiString newCaption = DirectoryListBox->Directory;
		if( changedFlag )
			newCaption += " *";
		Caption = newCaption;
	}
	void setChangedFlag( void )
	{
		changedFlag = true;
		SpeedButtonSave->Enabled = true;
		SpeedButtonRemove->Enabled = true;
		SpeedButtonUndo->Enabled = true;
		setCaption();
	}
	void clrChangedFlag( void )
	{
		changedFlag = false;
		SpeedButtonSave->Enabled = false;
		SpeedButtonUndo->Enabled = false;
		setCaption();
	}
public:		// User declarations
	__fastcall TIsMainForm(TComponent* Owner);
};
//---------------------------------------------------------------------------
extern PACKAGE TIsMainForm *IsMainForm;
//---------------------------------------------------------------------------
#endif
