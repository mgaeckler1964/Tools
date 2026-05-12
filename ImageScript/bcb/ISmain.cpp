//---------------------------------------------------------------------------
#include <fstream.h>

#include <io.h>
#include <vcl.h>
#include <vcl/jpeg.hpp>
#include <vcl/registry.hpp>

#include <gak/gaklib.h>
#include <gak/array.h>
#include <gak/directory.h>

#pragma hdrstop

#include "ISmain.h"
#define CONFIG_FILE	".files.cfg"

//---------------------------------------------------------------------------
#pragma package(smart_init)
#pragma resource "*.dfm"

using namespace gak;

TIsMainForm *IsMainForm;
//---------------------------------------------------------------------------
__fastcall TIsMainForm::TIsMainForm(TComponent* Owner)
	: TForm(Owner)
{
	changedFlag = false;
}
//---------------------------------------------------------------------------
void __fastcall TIsMainForm::ListBoxFilesClick(TObject *Sender)
{
	AnsiString file = ListBoxFiles->Items->Strings[ListBoxFiles->ItemIndex];

	if( isFile( file.c_str() ) )
	{
		AnsiString ext = ExtractFileExt(file);
		ext.Delete( 1, 1 );

		try
		{
			Image->Picture->LoadFromFile( file );
			PanelImageViewerResize( Sender );
		}
		catch( EInvalidGraphic &e )
		{
			// ignore
		}
	}
}
//---------------------------------------------------------------------------
void __fastcall TIsMainForm::FormCreate(TObject *)
{
	TPicture::RegisterFileFormat( NULL, "JPG", "JPEG Files", __classid( TJPEGImage ) );
}
//---------------------------------------------------------------------------
void TIsMainForm::save( void )
{
	if( changedFlag )
	{
		std::ofstream cs( configFile.c_str() );
		if( cs.is_open() )
		{
			for( int i=0; i<ListBoxFiles->Items->Count; i++ )
				cs << ListBoxFiles->Items->Strings[i].c_str() << '\n';
		}
		clrChangedFlag();
	}
}
//---------------------------------------------------------------------------
void __fastcall TIsMainForm::DirectoryListBoxChange(TObject *Sender)
{
	if( changedFlag )
		save();

	DirectoryList	dir;

	configFile = DirectoryListBox->Directory;
	TRegistry	*reg = new TRegistry;
	if( reg->OpenKey( "Software\\CRESD\\ImageScript", true ) )
	{
		reg->WriteString( "lastPath", configFile );
		reg->CloseKey();
	}
	delete reg;

	//	setcwd( configFile.c_str() );
	Caption = configFile;
	configFile += "\\" CONFIG_FILE;

	if( exists( CONFIG_FILE ) )
	{
		FILE *fp = fopen( CONFIG_FILE, "r" );
		if( fp )
		{
			DirectoryList	fileList;
			STRING			line;
			while( !feof( fp ) )
			{
				line << fp;
				if( !line.isEmpty() )
					dir.addElement( DirectoryEntry(line) );
			}
			fclose( fp );
			fileList.dirlist( "." );
			for(
				DirectoryList::const_iterator it = fileList.cbegin(), endIT = fileList.cend();
				it != endIT;
				++it
			)
			{
				const DirectoryEntry &curFile = *it;
				if( !dir.findElement( curFile ) )
				{
					dir.addElement( curFile );
				}
			}
		}
		SpeedButtonRemove->Enabled = true;
	}
	else
	{
		dir.dirlist( "." );
		SpeedButtonRemove->Enabled = false;
	}

	ListBoxFiles->Items->Clear();
	for(
		DirectoryList::const_iterator it = dir.cbegin(), endIT = dir.cend();
		it != endIT;
		++it
	)
	{
		const DirectoryEntry &curFile = *it;
		STRING file = curFile.fileName;
		if( file[0] != '.' )
		{
			FStype type = fileType( file );
			if( type != fsNOT_EXISTING )
			{
				if( type == fsDIRECTORY )
					file += DIRECTORY_DELIMITER;

				ListBoxFiles->Items->Add( (const char *)file );
			}
		}
	}
	if( ListBoxFiles->Items->Count )
	{
		ListBoxFiles->ItemIndex = 0;
		ListBoxFilesClick( Sender );
	}

	clrChangedFlag();
}
//---------------------------------------------------------------------------

void __fastcall TIsMainForm::PanelImageViewerResize(TObject *)
{
	int imgWidth = Image->Picture->Width;
	int imgHeight = Image->Picture->Height;

	if( imgWidth > 0 && imgHeight > 0 )
	{
		if( imgWidth  < PanelImageViewer->Width
		&&  imgHeight < PanelImageViewer->Height )
		{
			Image->Width = imgWidth;
			Image->Height = imgHeight;
		}
		else
		{
			double	ratio = (double)imgWidth/(double)imgHeight;

			int newWidth = PanelImageViewer->Width;
			int newHeight = newWidth / ratio;

			if( newHeight > PanelImageViewer->Height )
			{
				newHeight = PanelImageViewer->Height;
				newWidth = newHeight * ratio;
			}

			Image->Width = newWidth;
			Image->Height = newHeight;
		}
	}
}
//---------------------------------------------------------------------------


void __fastcall TIsMainForm::FormShow(TObject *Sender)
{

	TRegistry	*reg = new TRegistry;
	if( reg->OpenKey( "CRESD\\ImageScript", false )
	&& reg->ValueExists( "lastPath" ) )
	{
		AnsiString lastPath = reg->ReadString( "lastPath" );
		reg->CloseKey();

		DirectoryListBox->Directory = lastPath;
	}
	else
		DirectoryListBoxChange( Sender );

	delete reg;
}

//---------------------------------------------------------------------------

void __fastcall TIsMainForm::SpeedButtonUpClick(TObject *)
{
	int	curIndex = ListBoxFiles->ItemIndex;
	int newIndex = curIndex - 1;

	if( curIndex > 0 )
	{
		AnsiString curText = ListBoxFiles->Items->Strings[curIndex];
		AnsiString topText = ListBoxFiles->Items->Strings[newIndex];
		ListBoxFiles->Items->Strings[curIndex] = topText;
		ListBoxFiles->Items->Strings[newIndex] = curText;
		ListBoxFiles->ItemIndex = newIndex;

		setChangedFlag();
	}
}
//---------------------------------------------------------------------------

void __fastcall TIsMainForm::SpeedButtonDownClick(TObject *)
{
	int	curIndex = ListBoxFiles->ItemIndex;
	int newIndex = curIndex + 1;

	if( newIndex < ListBoxFiles->Items->Count )
	{
		AnsiString curText = ListBoxFiles->Items->Strings[curIndex];
		AnsiString lowText = ListBoxFiles->Items->Strings[newIndex];
		ListBoxFiles->Items->Strings[curIndex] = lowText;
		ListBoxFiles->Items->Strings[newIndex] = curText;
		ListBoxFiles->ItemIndex = newIndex;

		setChangedFlag();
	}
}
//---------------------------------------------------------------------------

void __fastcall TIsMainForm::FormClose(TObject *,
      TCloseAction &)
{
	if( changedFlag )
		save();
}
//---------------------------------------------------------------------------

void __fastcall TIsMainForm::SpeedButtonSaveClick(TObject *)
{
	if( changedFlag )
		save();
}
//---------------------------------------------------------------------------

void __fastcall TIsMainForm::SpeedButtonRemoveClick(TObject *Sender)
{
	if( exists( configFile.c_str() ) )
		unlink( configFile.c_str() );

	SpeedButtonUndoClick( Sender );
}
//---------------------------------------------------------------------------

void __fastcall TIsMainForm::SpeedButtonUndoClick(TObject *Sender)
{
	clrChangedFlag();
	DirectoryListBoxChange( Sender );
}
//---------------------------------------------------------------------------

